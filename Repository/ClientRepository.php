<?php

namespace FAC\UserBundle\Repository;

use Doctrine\Common\Persistence\ManagerRegistry;
use LogBundle\Document\LogMonitor;
use LogBundle\Service\LogMonitorService;
use Schema\SchemaEntityRepository;
use FAC\UserBundle\Entity\Client;
use Utils\LogUtils;

class ClientRepository extends SchemaEntityRepository {

    /** @var LogMonitorService $log_monitor */
    protected $log_monitor;

    ///////////////////////////////////////////
    /// CONSTRUCTOR

    /**
     * ClientRepository constructor.
     * @param ManagerRegistry $registry
     * @param LogMonitorService $logMonitorService
     */
    public function __construct(ManagerRegistry $registry, LogMonitorService $logMonitorService) {
        parent::__construct($registry, Client::class);
        $this->log_monitor = $logMonitorService;
    }

    /**
     * @param $id
     * @param $randomId
     * @return mixed|null
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
            $exception = LogUtils::getFormattedExceptions($e);
            $this->log_monitor->trace(LogMonitor::LOG_CHANNEL_QUERY, 500, "query.error", $exception);
        }

        return $result;
    }

}