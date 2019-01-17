<?php

namespace FAC\UserBundle\Repository;

use FAC\UserBundle\Entity\UserEmail;
use Schema\SchemaEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use DateTime;
use FAC\UserBundle\Entity\User;

class UserEmailRepository extends SchemaEntityRepository {

    ///////////////////////////////////////////
    /// CONSTRUCTOR

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, UserEmail::class);
    }

}