<?php

namespace FAC\UserBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use FAC\UserBundle\Entity\UserEmail;
use FAC\UserBundle\Utils\Utils;

class UserEmailRepository extends ServiceEntityRepository {

    ///////////////////////////////////////////
    /// CONSTRUCTOR

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, UserEmail::class);
    }

    ///////////////////////////////////////////
    /// FIND FUNCTIONS

    /**
     * Find one object by attributes.
     * @param array $criteria
     * @return UserEmail|null
     */
    public function findOne(array $criteria) {
        $object = null;

        if(count($criteria) > 0) {

            /** @var UserEmail $object */
            $object = $this->findOneBy(
                $criteria
            );
        }

        return $object;
    }

    /**
     * Saves a given entity.
     * @param  UserEmail $entity
     * @param  bool $update
     * @return bool|array
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function write(UserEmail $entity, $update = false) {
        $this->getEntityManager()->getConnection()->beginTransaction();

        try {
            if(!$update) {
                $this->getEntityManager()->persist($entity);
            }
            $this->getEntityManager()->flush();
            $this->getEntityManager()->getConnection()->commit();
        } catch (\Exception $e) {
            $exception = Utils::getFormattedExceptions($e);
            $this->getEntityManager()->getConnection()->rollBack();
            return $exception;
        }

        return true;
    }
}