<?php

namespace FAC\UserBundle\Service;


use FAC\UserBundle\Utils\Utils;
use FOS\OAuthServerBundle\Entity\ClientManager;
use LogBundle\Service\LogMonitorService;
use FAC\UserBundle\Entity\Client;
use FAC\UserBundle\Repository\ClientRepository;

class ClientService {

    /** @var LogMonitorService $log_monitor */
    protected $log_monitor;

    private $repository;

    ///////////////////////////////////////////
    /// CONSTRUCTOR

    /**
     * ClientService constructor.
     * @param ClientRepository $repository
     */
    public function __construct(ClientRepository $repository) {
        $this->repository  = $repository;
    }

    ///////////////////////////////////////////
    /// SELECT FUNCTIONS

    /**
     * Returns the entity given its id.
     * NULL will be returned if the entity does not exist.
     * @param int $id
     * @return Client|null
     */
    public function getById($id) {
        return $this->repository->findOne(array('id' => $id));
    }

    /**
     * Returns the entity given a unique attribute/s.
     * NULL will be returned if the entity does not exist.
     * @param array $attributes
     * @return Client|null
     */
    public function getOneByAttributes(array $attributes) {
        return $this->repository->findOne($attributes);
    }


    /**
     * @param $clientManager
     * @param Client $client
     * @return Client|array
     */
    public function create(ClientManager $clientManager, Client $client){
        $newClient = $clientManager->createClient();
        $newClient->setKeyword($client->getKeyword());
        $newClient->setRedirectUris($client->getRedirectUris());
        $newClient->setAllowedGrantTypes($client->getAllowedGrantTypes());

        try{
            $clientManager->updateClient($newClient);
        }catch (\Exception $e){
            $exception = Utils::getFormattedExceptions($e);
            return $exception;
        }

        return $client;
    }

    /**
     * @param null $clientId
     * @return mixed|null
     */
    public function getByClientId($clientId=null) {

        $client = null;

        if(!is_null($clientId)) {

            $clientArray = explode("_",$clientId);
            $id          = $clientArray[0];
            $randomId    = $clientArray[1];

            $client = $this->repository->findByClientId($id, $randomId);
        }

        return $client;
    }
}