<?php

namespace UserBundle\Repository;

use UserBundle\Entity\UserEmail;
use Schema\SchemaEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use DateTime;
use UserBundle\Entity\User;

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