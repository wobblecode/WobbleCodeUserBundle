<?php

namespace WobbleCode\UserBundle\OAuth;

use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use Buzz\Browser as BuzzBrowser;
use Buzz\Client\Curl as BuzzCUrl;
use Buzz\Message\Request as HttpRequest;
use Buzz\Message\Response as HttpResponse;
use WobbleCode\UserBundle\Document\Contact;
use WobbleCode\UserBundle\Document\Avatar;
use WobbleCode\UserBundle\Document\User;

class OAuthMapper
{
    public function updateContact(Contact $contact, $data)
    {
        if (!empty($data['name'])) {
            $contact->setName($data['name']);
        }

        if (!empty($data['firstName'])) {
            $contact->setName($data['firstName']);
        }

        if (!empty($data['lastName'])) {
            $contact->setLastNames($data['lastName']);
        }

        if (!empty($data['gender'])) {
            $contact->setGender($data['gender']);
        }

        if (!empty($data['timezone'])) {
            $contact->setTimezone($data['timezone']);
        }

        if (!empty($data['locale'])) {
            $contact->setLocale($data['locale']);
        }

        if (!empty($data['googleProfileLink'])) {
            $contact->setServiceProfile('google', ['link' => $data['googleProfileLink']]);
        }

        if (!empty($data['facebookProfileLink'])) {
            $contact->setServiceProfile('facebook', ['link' => $data['facebookProfileLink']]);
        }

        if (!empty($data['gitHubProfileLink'])) {
            $contact->setServiceProfile('github', ['link' => $data['gitHubProfileLink']]);
        }

        $avatar = $contact->getAvatar();

        if (!empty($data['gravatar'])) {
            $avatar->setGravatarData(['email' => $data['gravatar']]);
        }

        if (!empty($data['avatar'])) {
            $avatar->setSocialData(['default' => $data['avatar']]);
        }
    }

    /**
     * Get oAuth data and normalize it to same var names
     *
     * @param HttpMessageInterface $response oAuth response
     *
     * @return array with normalized data
     */
    public function normalizeData(PathUserResponse $response)
    {
        $return   = $response->getResponse();
        $provider = $response->getResourceOwner()->getName();
        $token    = $response->getAccessToken();

        switch ($provider) {
            case 'google':
                $data = $this->normalizeGoogle($return);
                break;

            case 'facebook':
                $data = $this->normalizeFacebook($return);
                break;

            case 'github':
                $data = $this->normalizeGithub($return, $token);
                break;
        }

        $data['provider'] = $provider;
        $data['token'] = $token;

        return $data;
    }

    /**
     * Mapping data from Google
     *
     * @param Reponse $response
     *
     * @return array contains normalized user data
     */
    public function normalizeGoogle($return)
    {
        $genders = [
            'male'   => 'M',
            'female' => 'F'
        ];

        $data = [
            'id'            => $return['id'],
            'name'          => $this->ensureUtf8($return['name']),
            'firstName'     => $this->ensureUtf8($return['given_name']),
            'lastName'      => $this->ensureUtf8($return['family_name']),
            'username'      => $this->ensureUtf8($return['id']),
            'email'         => $return['email'],
            'emailVerified' => $return['verified_email'],
            'gender'        => null,
            'locale'        => null,
            'timezone'      => null,
            'avatar'        => null,
            'gravatar'      => null,
            'isSilhouette'  => true,
        ];

        if (isset($return['gender'])) {
            $data['gender'] = $genders[$return['gender']];
        }

        if (isset($return['locale'])) {
            $data['locale'] = str_replace('-', '_', $return['locale']);
        }

        if (isset($return['link'])) {
            $data['googleProfileLink'] = $return['link'];
        }

        if (isset($return['picture'])) {
            $data['avatar'] = $this->ensureUtf8($return['picture']);
            $data['isSilhouette'] = false;
        }

        $data['avatar'] = strtok($data['avatar'], '?');

        return $data;
    }

    /**
     * Mapping data from facbook
     *
     * @todo replace Buzz with Guzzle
     *
     * @param Reponse $response
     *
     * @return array contains normalized user data
     */
    public function normalizeFacebook($return)
    {
        $genders = [
            'male'   => 'M',
            'female' => 'F'
        ];

        $data = [
            'id'                  => $return['id'],
            'name'                => $return['name'],
            'firstName'           => $return['first_name'],
            'lastName'            => $return['last_name'],
            'facebookProfileLink' => $return['link'],
            'username'            => $return['username'],
            'email'               => $return['email'],
            'timezone'            => $return['timezone'],
            'locale'              => $return['locale'],
            'verified'            => $return['verified'],
            'gender'              => false,
            'avatar'              => false,
            'gravatar'            => false,
            'emailVerified'       => true,
            'isSilhouette'        => true
        ];

        if (isset($return['gender'])) {
            $data['gender'] = $genders[$return['gender']];
        }

        $response = $this->httpRequest(
            'https://graph.facebook.com',
            '/'.$return['id'].'/?fields=picture.type(large)'
        );

        if ($response->isOk()) {
            $graph = json_decode($response->getContent());
            $data['avatar'] = $graph->picture->data->url;
            $data['issilhouette'] = $graph->picture->data->is_silhouette;
        }

        return $data;
    }

    /**
     * Mapping data from github
     *
     * @todo replace Buzz with Guzzle
     *
     * @param Reponse $response
     *
     * @return array contains normalized user data
     */
    public function normalizeGithub($return, $token)
    {
        $data = [
            'id'                => $return['id'],
            'username'          => $return['login'],
            'name'              => $return['name'],
            'email'             => $return['email'],
            'gravatar'          => $return['gravatar_id'],
            'avatar'            => $return['avatar_url'],
            'gitHubProfileLink' => $return['html_url'],
            'firstName'         => false,
            'lastName'          => false,
            'gender'            => false,
            'verified'          => true,
            'locale'            => false,
            'timezone'          => false,
            'isSilhouette'      => true,
            'emailVerified'     => false
        ];

        /**
         * Setup http://developer.github.com/v3/media/
         *
         * @todo application/vnd.github.v3
         */
        $data['email'] = $return['id'].'@github';

        $headers = ['Authorization: bearer '.$token];
        $response = $this->httpRequest('https://api.github.com', '/user/emails', null, $headers);


        if ($response->isOk()) {
            $emails = json_decode($response->getContent(), true);
            $lastEmail = end($emails);
            $data['email'] = $lastEmail['email'];
        }

        return $data;
    }

    /**
     * Performs an HTTP request
     *
     * @todo replace Buzz with Guzzle
     *
     * @param string $url     The url to fetch
     * @param string $content The content of the request
     * @param array  $headers The headers of the request
     * @param string $method  The HTTP method to use
     *
     * @return HttpMessageInterface The response content
     */
    protected function httpRequest($url, $resource = '/', $content = null, $headers = [], $method = 'GET')
    {
        $request = new HttpRequest($method, $resource, $url);
        $response = new HttpResponse();

        $headers = array_merge(['User-Agent: WobbleCodeUserBundle'], $headers);

        $request->setHeaders($headers);
        $request->setContent($content);

        $client = new BuzzCUrl();
        $browser = new BuzzBrowser($client);

        $browser->send($request, $response);

        return $response;
    }

    /**
     * Filters non-utf8 chars
     *
     * @param string
     *
     * @return string a valid utf8 string
     */
    protected function ensureUtf8($string)
    {
        return iconv("UTF-8", "UTF-8//IGNORE", $string);
    }
}
