<?php

namespace FAC\UserBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use FAC\UserBundle\Entity\Client;
use FAC\UserBundle\Utils\Utils;

class ClientRepository extends ServiceEntityRepository {

    ///////////////////////////////////////////
    /// CONSTRUCTOR

    /**
     * ClientRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Client::class);
    }

    ///////////////////////////////////////////
    /// FIND FUNCTIONS

    /**
     * Find one object by attributes.
     * @param array $criteria
     * @return Client|null
     */
    public function findOne(array $criteria) {
        $object = null;

        if(count($criteria) > 0) {

            /** @var Client $object */
            $object = $this->findOneBy(
                $criteria
            );
        }

        return $object;
    }

    /**
     * Find list objects by attributes given filters.
     * @param int $limit
     * @param int|null $offset
     * @param array $ordering
     * @param array $criteria
     * @return array
     */
    public function findList($limit = null, $offset = null, array $ordering = array(), array $criteria = array()) {
        if(count($criteria) > 0) {
            $list = $this->findBy(
                $criteria, (count($ordering)>0?$ordering:null), $limit, $offset
            );
        } else {
            $qb = $this->createQueryBuilder('entity');

            if(count($ordering) > 0) {
                $first = true;
                foreach ($ordering as $field => $type) {
                    if($first) {
                        $qb->orderBy('entity.'.$field, $type);
                        $first = false;
                    } else {
                        $qb->addOrderBy('entity.'.$field, $type);
                    }
                }
            }

            if($limit > 0) {
                $qb->setMaxResults($limit);
            }

            if($offset > 0) {
                $qb->setFirstResult($offset);
            }

            $list = $qb->getQuery()->getResult();
        }

        return $list;
    }

    /**
     * @param $id
     * @param $randomId
     * @return null
     */
    public function findByClientId ($id, $randomId) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('c')
            ->from('UserBundle:Client', 'c')
            ->where('c.isDisable=0 AND c.id=:id AND c.randomId=:randomId')
            ->setParameter('id',$id)
            ->setParameter('randomId',$randomId)
        ;

        $result = null;

        try {
            $result = $qb->getQuery()->getSingleResult();
        } catch (\Exception $e) {
            return null;
        }

        return $result;
    }

    ///////////////////////////////////////////
    /// OBJECT FUNCTIONS

    /**
     * Saves a given entity.
     * @param Client $entity
     * @param bool $update
     * @return bool|array
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function write(Client $entity, $update = false) {
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