<?php

namespace FAC\UserBundle\Controller;

use Exceptions\ValidationException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FAC\UserBundle\Entity\UserEmail;
use FAC\UserBundle\Form\EmailType;
use FAC\UserBundle\Entity\Client;
use FAC\UserBundle\Form\UserFullnameType;
use FAC\UserBundle\Service\ClientService;
use FAC\UserBundle\Service\UserEmailService;
use FAC\UserBundle\Service\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use FAC\UserBundle\Entity\User;
use FAC\UserBundle\Form\UserType;
use FAC\UserBundle\Utils\ResponseUtils;
use FAC\UserBundle\Utils\Utils;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;

class UserController extends FOSRestController {

    /**
     * Action used to get a User.
     *
     * @Rest\Get("/admin/user/{id_user}")
     * @ParamConverter("user", class="UserBundle:User", options={"id" = "id_user"})
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
     *     description="No User found.",
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
     *         @Model(type=User::class)
     *     )
     * ),
     * @SWG\Tag(name="User")
     *
     * @param  User $user
     * @param  integer $id_user
     * @return JsonResponse
     */
    public function showAction(User $user = null, $id_user){
        $response = new ResponseUtils($this->get("translator"));

        if(!Utils::checkId($id_user)){
            return $response->getResponse(array(), "parameter.id.invalid",400);
        }

        if(is_null($user)){
            return $response->getResponse(array(), "data.not.found.404",404);
        }

        return $response->getResponse($user->adminSerializer());
    }

    /**
     * Create new user account.
     *
     * @Rest\Post("/public/signup/{type}")
     *
     * @SWG\Parameter(
     *     name="User fields",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=UserType::class)
     *     ),
     *     description="UserType fields"
     * ),
     * @SWG\Parameter(
     *     name="User fields",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *          type="array",
     *          @SWG\Items(
     *              type="object",
     *              @SWG\Property(property="idCalendarTimezone", type="integer"),
     *              @SWG\Property(property="idClient", type="string"),
     *              ),
     *          ),
     *     ),
     *     description="User fields"
     * ),
     * @SWG\Response(
     *     response=400,
     *     description="The parameters are empty or invalid.",
     * ),
     * @SWG\Response(
     *     response=401,
     *     description="The user is unauthorized.",
     * ),
     * @SWG\Response(
     *     response=500,
     *     description="Error on saved occurred.",
     * ),
     * @SWG\Response(
     *     response=201,
     *     description="User created.",
     * ),
     * @SWG\Tag(name="User")
     * @param   string $type
     * @param   Request $request
     * @param   UserService $userService
     * @param   ValidationException $validationException
     * @return  JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function createAction($type,
                                 Request $request,
                                 UserService  $userService,
                                 ValidationException $validationException
    ) {
        $response = new ResponseUtils($this->get("translator"));

        $user = new User();
        $validator = $this->get('validator');

        $form = $this->createForm(UserType::class, $user);
        $data = json_decode($request->getContent(), true);

        $form->submit($data);
        $user = $userService->init(
            array(
                'email'         => $user->getEmail(),
                'plainPassword' => $user->getPlainPassword()
            ));

        $errors = $validator->validate($user, null, array("registration"));

        if(count($errors) > 0) {
            $formattedErrors = $validationException->getFormattedExceptions($errors);
            return $response->getResponse($formattedErrors, "parameters.invalid", 400);
        }

        if($type == "worker") {
            $user->addRole("ROLE_WORKER");
        } elseif($type == "business") {
            $user->addRole("ROLE_BUSINESS");
            $user->addRole("ROLE_REFERENT");
        }

        $user = $userService->create($user);
        if(!$user) {
            return $response->getResponse(array(), "error.save", 500);
        }

        return $response->getResponse($user->viewSerializer(), "success.save", 201);
    }

    /**
     * Confirm a new user account.
     *
     * @Rest\Get("/public/user/confirm/{id_user}/{token}")
     * @ParamConverter("user", class="UserBundle:User", options={"id" = "id_user"})
     *
     * @SWG\Response(
     *     response=400,
     *     description="The parameters are empty or invalid.",
     * ),
     * @SWG\Response(
     *     response=401,
     *     description="The user is unauthorized.",
     * ),
     * @SWG\Response(
     *     response=404,
     *     description="The user does not exist.",
     * ),
     * @SWG\Response(
     *     response=403,
     *     description="User already enabled.",
     * ),
     * @SWG\Response(
     *     response=500,
     *     description="Error on saved occurred.",
     * ),
     * @SWG\Response(
     *     response=200,
     *     description="User confirmed.",
     * ),
     * @SWG\Tag(name="User")
     * @param   User|null $user
     * @param   $id_user
     * @param   string $token
     * @param   UserService $userService
     * @return  JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function createConfirmAction(User $user = null, $id_user, $token, UserService  $userService) {
        $response = new ResponseUtils($this->get("translator"));

        if(!Utils::checkId($id_user)) {
            return $response->getResponse(array(), "parameter.id.invalid",400);
        }

        if(!Utils::checkHashString($token)) {
            return $response->getResponse(array(), "parameter.token.invalid",400);
        }

        if(is_null($user)){
            return $response->getResponse(array(), "data.not.found.404",404);
        }

        if($user->isEnabled()) {
            return $response->getResponse(array(), "user.already.enabled",403);
        }

        if(!$userService->confirmCreate($user, $token, null)) {
            return $response->getResponse(array(), "token.expired",400);
        }

        return $response->getResponse($user->viewSerializer(), 'success.save', 200);
    }

    /**
     * Resend confirmation email.
     *
     * @Rest\Get("/public/user/resend/confirm/{email}")
     *
     * @SWG\Response(
     *     response=400,
     *     description="The parameters are empty or invalid.",
     * ),
     * @SWG\Response(
     *     response=401,
     *     description="The user is unauthorized.",
     * ),
     * @SWG\Response(
     *     response=404,
     *     description="The user does not exist.",
     * ),
     * @SWG\Response(
     *     response=403,
     *     description="User already enabled.",
     * ),
     * @SWG\Response(
     *     response=500,
     *     description="Error on saved occurred.",
     * ),
     * @SWG\Response(
     *     response=200,
     *     description="User confirmed.",
     * ),
     * @SWG\Tag(name="User")
     * @param   string $email
     * @param   UserService $userService
     * @return  JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function resendMailConfirmationAction($email, UserService  $userService, EmailService $emailService) {
        $response = new ResponseUtils($this->get("translator"));

        if(!Utils::checkEmailString($email)) {
            return $response->getResponse(array(), "parameter.id.invalid",400);
        }

        /** @var User $user */
        $user = $userService->getByEmail($email);
        if(is_null($user)) {
            return $response->getResponse(array(), "error.email.invalid",400);
        }

        if(!$user->isEnabled()) {
            return $response->getResponse(array(), "error.email.invalid",400);
        }

        $just_sent_confirmation = $emailService->getJustSentConfirmation($user);
        if($just_sent_confirmation) {
            return $response->getResponse(array(), "error.email.invalid",400);
        }

        $token = $userService->confirmationToken($user);
        $user = $userService->sendMailRegistrationConfirm($user, $token);
        if(is_null($user)) {
            return $response->getResponse(array(), "error.email.invalid",400);
        }

        if(!$userService->save($user)) {
            return $response->getResponse(array(), "error.save", 500);
        }

        return $response->getResponse(array(), "success.email.sending",200);
    }

    /**
     * Reset password user.
     *
     * @Rest\Get("/public/user/reset/{email}")
     *
     * @SWG\Response(
     *     response=400,
     *     description="The parameters are empty or invalid.",
     * ),
     * @SWG\Response(
     *     response=401,
     *     description="The user is unauthorized.",
     * ),
     * @SWG\Response(
     *     response=404,
     *     description="The user does not exist.",
     * ),
     * @SWG\Response(
     *     response=403,
     *     description="User already enabled.",
     * ),
     * @SWG\Response(
     *     response=500,
     *     description="Error on saved occurred.",
     * ),
     * @SWG\Response(
     *     response=200,
     *     description="Request password reset sended.",
     * ),
     * @SWG\Tag(name="User")
     * @param   string $email
     * @param   UserService $userService
     * @return  JsonResponse
     * @throws  \Doctrine\DBAL\ConnectionException
     */
    public function resetAction($email, UserService  $userService) {
        $response = new ResponseUtils($this->get("translator"));

        if(!Utils::checkEmailString($email)) {
            return $response->getResponse(array(), "parameter.id.invalid",400);
        }

        /** @var User $user */
        $user = $userService->getByEmail($email);
        if(is_null($user)) {
            return $response->getResponse(array(), "error.email.invalid",400);
        }

        if(!$user->isEnabled()) {
            return $response->getResponse(array(), "error.email.invalid",400);
        }

        if(!is_null($user->getPasswordRequestedAt())) {
            if(time()-$user->getPasswordRequestedAt()->getTimestamp() < $userService->delay_reset) {
                return $response->getResponse(array(), "many.requests",429);
            }
        }

        $user = $userService->reset($user);
        if(is_null($user)) {
            return $response->getResponse(array(), "error.save", 500);
        }

        return $response->getResponse(array(), "success.email.sending",200);
    }

    /**
     * Reset password confirm.
     *
     * @Rest\Post("/public/user/reset/confirm/{id_user}/{token}")
     * @ParamConverter("user", class="UserBundle:User", options={"id" = "id_user"})
     *
     * @SWG\Parameter(
     *     name="new_password",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(type="integer"),
     *     description="New password"
     * ),
     * @SWG\Response(
     *     response=400,
     *     description="The parameters are empty or invalid.",
     * ),
     * @SWG\Response(
     *     response=401,
     *     description="The user is unauthorized.",
     * ),
     * @SWG\Response(
     *     response=404,
     *     description="The user does not exist.",
     * ),
     * @SWG\Response(
     *     response=403,
     *     description="User is not enabled.",
     * ),
     * @SWG\Response(
     *     response=500,
     *     description="Error on saved occurred.",
     * ),
     * @SWG\Response(
     *     response=200,
     *     description="User reset password.",
     * ),
     * @SWG\Tag(name="User")
     * @param   User $user
     * @param   $id_user
     * @param   string $token
     * @param   UserService $userService
     * @param   Request $request
     * @param   ValidationException $validationException
     * @return  JsonResponse
     * @throws  \Doctrine\DBAL\ConnectionException
     */
    public function resetConfirmAction(User $user, $id_user, $token, UserService $userService, Request $request, ValidationException $validationException) {
        $response = new ResponseUtils($this->get("translator"));

        if(!Utils::checkId($id_user)) {
            return $response->getResponse(array(), "parameter.id.invalid",400);
        }

        if(!Utils::checkHashString($token)) {
            return $response->getResponse(array(), "parameter.token.invalid",400);
        }

        if(is_null($user)){
            return $response->getResponse(array(), "data.not.found.404",404);
        }

        if(!$user->isEnabled()) {
            return $response->getResponse(array(), "user.not.already.enabled",403);
        }

        $data = json_decode($request->getContent(), true);
        if(!isset($data['new_password']) || !Utils::checkPasswordString($data['new_password'])) {
            return $response->getResponse(array(), "parameters.invalid",400);
        }

        $user->setPlainPassword($data['new_password']);
        $validator = $this->get('validator');
        $errors = $validator->validate($user, null, array("registration"));
        if(count($errors) > 0) {
            $formattedErrors = $validationException->getFormattedExceptions($errors);
            return $response->getResponse($formattedErrors, "parameters.invalid",400);
        }

        if(!$userService->confirmReset($user, $token)) {
            return $response->getResponse(array(), "parameter.token.invalid",400);
        }

        return $response->getResponse(array(), 'reset.password.success', 200);
    }

    /**
     * Logout the logged user
     *
     * @Rest\Get("/private/user/logout")
     *
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     description="Authorization token: Bearer <token>"
     * ),
     * @SWG\Response(
     *     response=401,
     *     description="The user is unauthorized.",
     * ),
     * @SWG\Response(
     *     response=404,
     *     description="The user does not exist.",
     * ),
     * @SWG\Response(
     *     response=403,
     *     description="User is not enabled.",
     * ),
     * @SWG\Response(
     *     response=500,
     *     description="Error server.",
     * ),
     * @SWG\Response(
     *     response=200,
     *     description="User successfully logout.",
     * ),
     * @SWG\Tag(name="User")
     * @param   Request $request
     * @return  JsonResponse
     */
    public function logoutAction(Request $request) {
        $response = new ResponseUtils($this->get("translator"));

        $this->get('security.token_storage')->setToken(null);
        $request->getSession()->invalidate();

        return $response->getResponse(array(), "success.logout",200);
    }

    /**
     * Refresh password.
     *
     * @Rest\Put("/private/user/refresh")
     *
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     description="Authorization token: Bearer <token>"
     * ),
     * @SWG\Parameter(
     *     name="old_password",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(type="integer"),
     *     description="Old password"
     * ),
     * @SWG\Parameter(
     *     name="new_password",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(type="integer"),
     *     description="New password"
     * ),
     * @SWG\Response(
     *     response=401,
     *     description="The user is unauthorized.",
     * ),
     * @SWG\Response(
     *     response=404,
     *     description="The user does not exist.",
     * ),
     * @SWG\Response(
     *     response=403,
     *     description="User is not enabled.",
     * ),
     * @SWG\Response(
     *     response=500,
     *     description="Error saved occurred.",
     * ),
     * @SWG\Response(
     *     response=200,
     *     description="User successfully refreshed.",
     * ),
     * @SWG\Tag(name="User")
     * @param  Request $request
     * @param  UserService $userService
     * @param  ValidationException $validationException
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function refreshAction(Request $request, UserService $userService, ValidationException $validationException) {
        $response = new ResponseUtils($this->get("translator"));

        $data = json_decode($request->getContent(), true);
        if(!Utils::checkPasswordString($data['old_password'])) {
            return $response->getResponse(array(), "parameters.invalid", 400);
        }

        if(!Utils::checkPasswordString($data['new_password'])) {
            return $response->getResponse(array(), "parameters.invalid", 400);
        }

        $old_password = $data['old_password'];
        $new_password = $data['new_password'];

        if($new_password == $old_password) {
            return $response->getResponse(array(), "passwords.equals", 400);
        }

        $user = $this->getUser();
        if(!$user) {
            return $response->getResponse(array(), "user.inexistent", 400);
        }

        if(!$user->isEnabled()) {
            return $response->getResponse(array(), "user.not.already.enabled",403);
        }

        if(!$this->get('security.password_encoder')->isPasswordValid($user, $old_password)) {
            return $response->getResponse(array(), "user.not.valid.password",403);
        }

        $user->setPlainPassword($new_password);
        $validator = $this->get('validator');
        $errors = $validator->validate($user, null, array("registration"));
        if(count($errors) > 0) {
            $formattedErrors = $validationException->getFormattedExceptions($errors);
            return $response->getResponse($formattedErrors, "parameters.invalid", 400);
        }

        $userService->updateCredentials($user);
        if(!$userService->save($user)) {
            return $response->getResponse(array(), "error.save", 500);
        }

        return $response->getResponse($user->viewSerializer(), "refresh.password.confirmed", 200);
    }

    /**
     * Verify password of the current user.
     *
     * @Rest\Post("/private/user/verify/password")
     *
     * @SWG\Parameter(
     *     name="plain_password",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *         type="string",
     *     ),
     *     description="The user plain password field"
     * ),
     * @SWG\Response(
     *     response=400,
     *     description="The parameters are empty or invalid.",
     * ),
     * @SWG\Response(
     *     response=401,
     *     description="The user is unauthorized.",
     * ),
     * @SWG\Response(
     *     response=404,
     *     description="The user does not exist.",
     * ),
     * @SWG\Response(
     *     response=403,
     *     description="User already enabled.",
     * ),
     * @SWG\Response(
     *     response=500,
     *     description="Error on saved occurred.",
     * ),
     * @SWG\Response(
     *     response=200,
     *     description="User confirmed.",
     * ),
     * @SWG\Tag(name="User")
     * @param   Request $request
     * @param   UserService $userService
     * @return  JsonResponse
     */
    public function verifyPasswordAction(Request $request, UserService $userService) {
        $response = new ResponseUtils($this->get("translator"));

        /** @var User $user */
        $user = $this->getUser();
        if(is_null($user)){
            return $response->getResponse(array(), "data.not.found.404",404);
        }

        if(!$user->isEnabled()) {
            return $response->getResponse(array(), "user.not.enabled",403);
        }

        // Get encoded Password to confirmation

        $data = json_decode($request->getContent(), true);
        $plainPassword = isset($data["plainPassword"]) ? $data["plainPassword"]:null;

        if(empty($plainPassword)) {
            return $response->getResponse(array(), "empty.password",400);
        }

        if(!$userService->isPasswordValid($user, $plainPassword)){
            return $response->getResponse(array(), "password.incorrect",400);
        }

        return $response->getResponse($user->viewSerializer(), 'success.save', 200);
    }


    /** Update email of an user.
     *
     * @Rest\Put("/private/user/change-email/{id}/{email}")
     *
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     description="Authorization token: Bearer <token>"
     * ),
     * @SWG\Parameter(
     *     name="Email fields",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=EmailType::class)
     *     ),
     *     description="EmailType fields"
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
     *         @Model(type=User::class)
     *     )
     * ),
     * @SWG\Tag(name="User")
     * @param   User|null $user
     * @param   int $id
     * @param   $email
     * @param   UserEmailService $userEmailService
     * @param   UserService $userService
     * @return  JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function updateEmailAction(User $user=null, $id,
                                      $email,
                                      UserEmailService $userEmailService,
                                      UserService $userService
    ) {
        $response  = new ResponseUtils($this->get("translator"));

        if(!Utils::checkId($id)){
            return $response->getResponse(array(), "parameter.id.invalid",400);
        }

        if(null === $user){
            return $response->getResponse(array(), "data.not.found.404",404);
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if( ($currentUser->getId() != $id &&
                (!in_array("ROLE_ADMIN", $currentUser->getRoles()) ||
                    !in_array("ROLE_SUPER_ADMIN", $currentUser->getRoles())
                )
            ) || (!$currentUser->isEnabled() || !$user->isEnabled())
        ) {
            return $response->getResponse(array(),"user.not.rights", 403);
        }

        if(!Utils::checkEmailString($email)) {
            return $response->getResponse(array(), "error.email.invalid",400);
        }

        $userEmail = $userEmailService->getOneByAttributes(array('email' => $email));
        if(!is_null($userEmail)) {
            return $response->getResponse(array(), "error.email.already.sended",400);
        }

        if(!$userService->save($user)) {
            return $response->getResponse(array(), "error.save", 500);
        }

        /** @var UserEmail $userEmail */
        if(!$userEmail = $userEmailService->create($user, $email, false)){
            return $response->getResponse(array(), "error.save", 500);
        }

        $user = $userService->sendMailChangeEmailConfirm($user, $email);
        if(is_null($user)) {
            return $response->getResponse(array(), "error.email.invalid",400);
        }

        return $response->getResponse(array(), "success.email.sending",200);
    }

    /** Check if email exists.
     *
     * @Rest\Get("/public/verify-email/{email}")
     *
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
     *         @Model(type=User::class)
     *     )
     * ),
     * @SWG\Tag(name="User")
     * @param   $email
     * @param   UserService $userService
     * @return  JsonResponse
     */
    public function checkEmailExistsAction($email,
                                           UserService $userService) {
        $response  = new ResponseUtils($this->get("translator"));

        if(!Utils::checkEmailString($email)){
            return $response->getResponse(array(), "error.email.invalid",400);
        }

        if(!$userService->checkEmailExists($email)) {
            return $response->getResponse(array(), "data.not.found.404",404);
        }
        else {
            return $response->getResponse(array(), "success",200);
        }
    }

    /**
     * Change email confirm.
     *
     * @Rest\Get("/public/user/change-email/confirm/{id_user}/{email}/{token}")
     * @ParamConverter("user", class="UserBundle:User", options={"id" = "id_user"})
     *
     * @SWG\Response(
     *     response=400,
     *     description="The parameters are empty or invalid.",
     * ),
     * @SWG\Response(
     *     response=401,
     *     description="The user is unauthorized.",
     * ),
     * @SWG\Response(
     *     response=404,
     *     description="The user does not exist.",
     * ),
     * @SWG\Response(
     *     response=403,
     *     description="User is not enabled.",
     * ),
     * @SWG\Response(
     *     response=500,
     *     description="Error on saved occurred.",
     * ),
     * @SWG\Response(
     *     response=200,
     *     description="User reset password.",
     * ),
     * @SWG\Tag(name="User")
     * @param   User $user
     * @param   $id_user
     * @param   $email
     * @param   string $token
     * @param   UserService $userService
     * @param   UserEmailService $userEmailService
     * @param   ValidationException $validationException
     * @return  JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function resetEmailConfirmAction(User $user, $id_user, $email, $token, UserService $userService, UserEmailService $userEmailService, ValidationException $validationException) {
        $response = new ResponseUtils($this->get("translator"));

        if(!Utils::checkId($id_user)) {
            return $response->getResponse(array(), "parameter.id.invalid",400);
        }

        if(!Utils::checkHashString($token)) {
            return $response->getResponse(array(), "parameter.token.invalid",400);
        }

        if(is_null($user)){
            return $response->getResponse(array(), "data.not.found.404",404);
        }

        $validator = $this->get('validator');

        $form = $this->createForm(EmailType::class, $user);
        $data = array('email' => $email);

        $form->submit($data);
        $errors = $validator->validate($user, null, array("email"));

        if(count($errors) > 0) {
            $formattedErrors = $validationException->getFormattedExceptions($errors);
            return $response->getResponse($formattedErrors, "parameters.invalid",400);
        }

        /** @var User $userCheck */
        $userCheck = $userService->getOneByAttributes(array('email' => $email));
        if(!is_null($userCheck)) {
            return $response->getResponse(array(), "parameters.invalid",400);
        }

        $newEmail = $user->getEmail();

        $user->setEmailCanonical($newEmail);
        $user->setUsername($newEmail);
        $user->setUsernameCanonical($newEmail);

        /** @var UserEmail $userEmail */
        $userEmail = $userEmailService->getOneByAttributes(array('email' => $email));
        if(is_null($userEmail)) {
            return $response->getResponse(array(), "error.save",500);
        }

        if(!$userEmailService->updateStatus($userEmail, true)){
            return $response->getResponse(array(), "error.save", 500);
        }

        $user = $userService->save($user, $user, true);
        if(!$user) {
            return $response->getResponse(array(), "error.save",500);
        }

        return $response->getResponse(array(), "success",200);
    }

}
