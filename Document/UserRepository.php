<?php

namespace WobbleCode\UserBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

class UserRepository extends DocumentRepository
{
    public function findUniqueContactCellPhoneBy($criteria)
    {
        $cellPhone = $criteria['contact']->getCellPhone();

        if ($cellPhone === null) {
            return [];
        }

        return $this->findBy(['contact.cellPhone' => $cellPhone]);
    }
}
