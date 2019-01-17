<?php

namespace FAC\UserBundle\Controller;


use Exceptions\ValidationException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FAC\UserBundle\Entity\Client;
use FAC\UserBundle\Form\ClientType;
use FAC\UserBundle\Service\ClientService;
use FAC\UserBundle\Utils\ResponseUtils;
use FAC\UserBundle\Utils\Utils;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;

class ClientController extends FOSRestController {

    /**
     * Get a list of all clients.
     *
     * @Rest\Get("/admin/client/list")
     *
     * @SWG\Response(
     *     response=400,
     *     description="The suggest is not valid.",
     * ),
     * @SWG\Response(
     *     response=403,
     *     description="The user has not rights to read.",
     * ),
     * @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Client::class)
     *     )
     * ),
     * @param   ClientService $clientService
     * @return  JsonResponse
     */
    public function listAction(ClientService $clientService) {
        $response = new ResponseUtils($this->get("translator"));

        $clients = $clientService->getList('adminSerializer', array("isDisable"=>'0'));

        return $response->getListResponse($clients);
    }

    /**
     * Action used to get a Client.
     *
     * @Rest\Get("/admin/client/{id}")
     *
     * @SWG\Response(
     *     response=400,
     *     description="The parameter is empty or invalid.",
     * ),
     * @SWG\Response(
     *     response=401,
     *     description="The user is unauthorized.",
     * ),
     * @SWG\Response(
     *     response=403,
     *     description="The user is not authenticated.",
     * ),
     * @SWG\Response(
     *     response=404,
     *     description="No Client found.",
     * ),
     * @SWG\Response(
     *     response=500,
     *     description="Server error occurred.",
     * ),
     * @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Client::class)
     *     )
     * ),
     * @SWG\Tag(name="Client")
     *
     * @param  Client $client
     * @param  integer $id
     * @return JsonResponse
     */
    public function showAction(Client $client=null, $id){
        $response = new ResponseUtils($this->get("translator"));

        if(!Utils::checkId($id)){
            return $response->getResponse(array(), "parameter.id.invalid",400);
        }

        if(is_null($client)){
            return $response->getResponse(array(), "data.not.found.404",404);
        }

        return $response->getResponse($client->adminSerializer());
    }

    /**
     * Create a new client.
     *
     * @Rest\Post("/super/client")
     *
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     description="Authorization token: Bearer <token>"
     * ),
     * @SWG\Parameter(
     *     name="Client fields",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=ClientType::class)
     *     ),
     *     description="ClientType fields"
     * ),
     * @SWG\Response(
     *     response=400,
     *     description="The parameters are empty or invalid.",
     * ),
     * @SWG\Response(
     *     response=500,
     *     description="Error on saved occurred.",
     * ),
     * @SWG\Response(
     *     response=201,
     *     description="Success.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Client::class)
     *     )
     * ),
     * @SWG\Tag(name="Client")
     * @param   Request $request
     * @param   ClientService $clientService
     * @param   ValidationException $validationException
     * @return  JsonResponse
     */
    public function createAction(Request $request, ClientService $clientService, ValidationException $validationException) {
        $response  = new ResponseUtils($this->get("translator"));

        $client    = new Client();
        $validator = $this->get('validator');

        $form = $this->createForm(ClientType::class, $client);
        $data = json_decode($request->getContent(), true);

        $form->submit($data);
        $errors = $validator->validate($client);
        if(count($errors) > 0) {
            $formattedErrors = $validationException->getFormattedExceptions($errors);
            return $response->getResponse($formattedErrors, "parameters.invalid",400);
        }

        $client = $clientService->create($this->get('fos_oauth_server.client_manager.default'), $client);
        if(is_array($client)) {
            return $response->getResponse(array(), "error.save",500);
        }

        return $response->getResponse($client->adminSerializer(), "success.save", 201);
    }

    /**
     * Update a client.
     *
     * @Rest\Put("/super/client/{id}")
     *
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     description="Authorization token: Bearer <token>"
     * ),
     * @SWG\Parameter(
     *     name="Client fields",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=ClientType::class)
     *     ),
     *     description="ClientType fields"
     * ),
     * @SWG\Response(
     *     response=400,
     *     description="The parameters are empty or invalid.",
     * ),
     * @SWG\Response(
     *     response=500,
     *     description="Error on saved occurred.",
     * ),
     * @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Client::class)
     *     )
     * ),
     * @SWG\Tag(name="Client")
     * @param   Client|null $client
     * @param   int $id
     * @param   Request $request
     * @param   ClientService $clientService
     * @param   ValidationException $validationException
     * @return  JsonResponse
     * @throws  \Doctrine\DBAL\ConnectionException
     */
    public function updateAction(Client $client=null, $id, Request $request, ClientService $clientService, ValidationException $validationException) {
        $response  = new ResponseUtils($this->get("translator"));

        if(!Utils::checkId($id)){
            return $response->getResponse(array(), "parameter.id.invalid",400);
        }

        if(is_null($client)){
            return $response->getResponse(array(), "data.not.found.404",404);
        }

        $validator = $this->get('validator');

        $form = $this->createForm(ClientType::class, $client);
        $data = json_decode($request->getContent(), true);

        $form->submit($data);
        $errors = $validator->validate($client);

        if(count($errors) > 0) {
            $formattedErrors = $validationException->getFormattedExceptions($errors);
            return $response->getResponse($formattedErrors, "parameters.invalid",400);
        }

        if(!$clientService->save($client)) {
            return $response->getResponse(array(), "error.save",500);
        }

        return $response->getResponse($client->adminSerializer(), "success.save");
    }

    /**
     * Delete a client.
     *
     * @Rest\Delete("/super/client/{id}")
     *
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     description="Authorization token: Bearer <token>"
     * ),
     * @SWG\Response(
     *     response=400,
     *     description="The parameters are empty or invalid.",
     * ),
     * @SWG\Response(
     *     response=500,
     *     description="Error on saved occurred.",
     * ),
     * @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Client::class)
     *     )
     * ),
     * @SWG\Tag(name="Client")
     * @param   Client|null $client
     * @param   int $id
     * @param   ClientService $clientService
     * @return  JsonResponse
     * @throws  \Doctrine\DBAL\ConnectionException
     */
    public function deleteAction(Client $client=null, $id, ClientService $clientService) {
        $response  = new ResponseUtils($this->get("translator"));

        if(!Utils::checkId($id)){
            return $response->getResponse(array(), "parameter.id.invalid",400);
        }

        if(is_null($client)){
            return $response->getResponse(array(), "data.not.found.404",404);
        }

        $user = $this->getUser();
        if(!$user) {
            return $response->getResponse(array(), "user.inexistent", 400);
        }

        if(!$clientService->delete($client, $user)) {
            return $response->getResponse(array(), "error.save",500);
        }

        return $response->getResponse(array(),"success.delete");
    }
}
