<?php

namespace WobbleCode\UserBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

class InvitationRepository extends DocumentRepository
{
    public function findUniqueBy($criteria)
    {
        return $this->findBy([
            'organization.$id' => new \MongoId($criteria['organization']->getId()),
            'email' => $criteria['email'],
            'status' => $criteria['status']
        ]);
    }
}
