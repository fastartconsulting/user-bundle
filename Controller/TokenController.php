<?php

namespace UserBundle\Controller;


use FOS\OAuthServerBundle\Controller\TokenController as BaseController;
use OAuth2\OAuth2;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\Client;
use UserBundle\Service\ClientService;
use Utils\CurlUtils;

class TokenController extends BaseController {

    /**
     * @var ClientService $clientService
     */
    private $clientService;

    /**
     * TokenController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container ) {
        $server = $container->get("fos_oauth_server.server.public");

        parent::__construct($server);
        $this->clientService = $container->get("UserBundle\Service\ClientService");
    }

    public function tokenAction(Request $request) {

        $result = parent::tokenAction($request);

        //$params = $request->query->all();
        //$this->checkClientCardio($params,$result);

        return $result;
    }


    private function checkClientCardio (&$params,$result) {

        /**
         * Get Client by client_di param
         */
        if(!empty($params) && isset($params["client_id"])) {

            /** @var Client $client */
            $client = $this->clientService->getByClientId($params["client_id"]);
            if(!is_null($client)) {

                /**
                 * Get Keyword of client and check if it is for Cardio
                 */
                $keyword = $client->getKeyword();

                if(stripos($keyword,"cardio") !== false) {
                    $this->curlClientCardio($client,$result);
                }
            }
        }
    }


    private function curlClientCardio (Client &$client, $result) {
        /**
         * Get Redirect Uris. For convention the second is to save login data
         */
        $redirectUris = $client->getRedirectUris();
        if(!empty($redirectUris) && is_array($redirectUris)) {
            $redirectUri = $redirectUris[0];
            if(isset($redirectUris[1])) {
                $redirectUri = $redirectUris[1];
            }

            $content = $result->getContent();
            $postParams = json_decode($content,true);

            CurlUtils::curlPost($redirectUri,null, $postParams);
        }
    }

}