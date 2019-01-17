<?php

namespace UserBundle\Service;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\UserBundle\Model\UserManagerInterface;
use JMS\Serializer\Tests\Fixtures\Log;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Twig_Environment;
use UserBundle\Entity\AccessToken;
use UserBundle\Entity\User;
use UserBundle\Entity\UserEmail;
use UserBundle\Repository\UserRepository;

class UserService {

    ///////////////////////////////////////////
    /// CONSTRUCTOR

    private $um;

    private $encoder;

    private $mailer;

    private $uri;

    /** @var Twig_Environment $templating */
    protected $templating;

    /**
     * Number of seconds delay between tow resets
     * @var integer $delay_reset
     */
    public $delay_reset = 3600;

    /**
     * Number of seconds credentials validity
     * @var integer $timeout_credentials
     */
    public $timeout_credentials = 31500000;

    private $userEmailService;

    private $administration_address;

    private $sender_name;

    private $swiftMailer;

    /**
     * UserService constructor.
     * @param UserRepository $repository
     * @param UserManagerInterface $userManager
     * @param UserPasswordEncoderInterface $encoder
     * @param string $mailer_user
     * @param string $sender_name
     * @param Swift_Mailer $swift_mailer
     * @param Twig_Environment $templating
     * @param UserEmailService $userEmailService
     */
    public function __construct(
        UserRepository $repository,
        UserManagerInterface $userManager,
        UserPasswordEncoderInterface $encoder,
        string $mailer_user,
        string $sender_name,
        Swift_Mailer $swift_mailer,
        ///EmailService $mailer,
        Twig_Environment $templating,
        UserEmailService $userEmailService
    ) {
        $this->um                = $userManager;
        $this->encoder           = $encoder;
        $this->administration_address = $mailer_user;
        $this->sender_name = $sender_name;
        $this->swiftMailer = $swift_mailer;
        $this->repository        = $repository;
        $this->templating        = $templating;
        $this->userEmailService  = $userEmailService;
        $this->uri               = "";
    }

    /**
     * Get an user given its email.
     * NULL will be returned if the user does not exist or it is locked or its creator is locked.
     * @param string $email
     * @return User|object
     */
    public function getByEmail($email) {
        /** @var User $user */
        $user = $this->repository->findByEmail($email);

        if(is_null($user) || $user->isLocked()) {
            return null;
        }

        return $user;
    }

    /**
     * @param $username
     * @return null|User
     */
    public function getByUsername($username) {
        /** @var User $user */
        $user = $this->repository->findByUsername($username);

        if(is_null($user) || $user->isLocked()) {
            return null;
        }

        return $user;
    }

    /**
     * If email exists.
     * @param string $email
     * @return bool
     */
    public function checkEmailExists($email) {
        /** @var User $user */
        $user = $this->repository->findByEmail($email);

        if(is_null($user)) {
            return false;
        }

        return true;
    }

    /**
     * Initialize a user object
     * @param array $user_data
     * @return User $user
     */
    public function init(array $user_data) {
        /** @var User $user */
        $user = $this->getByEmail($user_data['email']);
        if(!is_null($user)) {
            if(!$user->hasRole("ROLE_USER")) {
                $user->setPlainPassword($user_data['plainPassword']);
                $user->setEnabled(false);
                $user->setLocked(false);
                return $user;
            }
        }

        $user = $this->um->createUser();
        $user->setEmail($user_data['email']);
        $user->setEmailCanonical($user_data['email']);
        $user->setUsername($user_data['email']);
        $user->setUsernameCanonical($user_data['email']);
        $user->setPlainPassword($user_data['plainPassword']);
        $user->setEnabled(false);
        $user->setLocked(false);

        return $user;
    }

    /**
     * Method to return encoded password
     *
     * @param User $user
     * @param null $plainPassword
     * @return string
     */
    public function getEncodedPassword(User $user, $plainPassword=null) {
        if(is_null($plainPassword)) {
            $plainPassword = $user->getPlainPassword();
        }
        return $this->encoder->encodePassword($user, $plainPassword);
    }

    /**
     * @param  User $user
     * @param  null $plainPassword
     * @return bool
     */
    public function isPasswordValid(User $user, $plainPassword=null) {
        if(is_null($plainPassword)) {
            $plainPassword = $user->getPlainPassword();
        }
        return $this->encoder->isPasswordValid($user, $plainPassword);
    }

    /**
     * Finalize and save the creation of an user.
     * Returns NULL if some error occurs otherwise it returns the persisted object.
     * @param User $user
     * @param $keyword
     * @return User|false
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function create(User $user) {
        $expiration = new \DateTime();
        $expiration->setTimestamp(time()+$this->timeout_credentials);
        $password = $this->getEncodedPassword($user);

        $currentTime = Utils::getCurrentTime();
        $user->setCreatedOn($currentTime);
        $user->setCredentialsExpired(false);
        $user->setCredentialsExpireAt($expiration);
        $user->setPassword($password);
        $token = $this->confirmationToken($user);

        $save_user = $this->repository->saveUser($user, $token, $this);
        if(is_array($save_user)) {
            return false;
        }

        return $user;
    }

    /**
     * Given the user returns a random token
     * @param User $user
     * @return string
     */
    public function generateToken(User $user) {
        return sha1(md5(rand(0,1000)).$user->getEmail().$user->getPassword().time());
    }

    /**
     * Given the user and token returns the mail confirmation link
     * @param User $user
     * @param string $token
     * @return string
     */
    private function urlConfirmRegistration(User $user, $token) {
        $uri = $this->uri.'/public/user/confirm';

        return $uri."/".$user->getId()."/" . $token;
    }

    /**
     * @param User $user
     * @return string
     */
    public function confirmationToken(User $user) {
        $token = $user->getConfirmationToken();
        if(is_null($token)) {
            $token = $this->generateToken($user);
        }

        $user->setConfirmationToken($token);

        return $token;
    }

    /**
     * Sends the registration mail asking to confirm the mail address
     * @param  User $user
     * @param  $token
     * @return User|null $user
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function sendMailRegistrationConfirm(User $user, $token) {
        $url = $this->urlConfirmRegistration($user, $token);

        try {
            $message = \Swift_Message::newInstance()
                ->setSubject("Registration confirm")
                ->setFrom([$this->administration_address => $this->sender_name])
                ->setTo($user->getEmail())
                ->setContentType('text/html')
                ->setBody($this->templating->render(
                    "email/registration.email.twig",
                    array(
                        'user'              => $user,
                        'confirmationUrl'   => $url
                    )
                ))
            ;

            if(!Utils::checkEmailString($user->getEmail())) {
                return null;
            }

            else {
                if(!$this->swiftMailer->send($message))
                    return null;
            }

        } catch (Exception $e) {


            return null;
        }

        return $user;
    }

    /**
     * Sends the registration mail that confirm successful registration
     * @param User $user
     * @return bool
     * @throws \Doctrine\DBAL\ConnectionException
     */
    private function sendMailRegistrationSuccessful(User $user) {

        try {
            $message = \Swift_Message::newInstance()
                ->setSubject("Registration completed successfully")
                ->setFrom([$this->administration_address => $this->sender_name])
                ->setTo($user->getEmail())
                ->setContentType('text/html')
                ->setBody($this->templating->render(
                    "email/registration_successful.email.twig",
                    array(
                        'user' => $user
                    )
                ))
            ;

            if(!Utils::checkEmailString($user->getEmail())) {
                return false;
            }

            else {
                if(!$this->swiftMailer->send($message))
                    return false;
            }

        } catch (\Exception $e) {


            return false;
        }

        return true;
    }

    /**
     * Given the user and relative token confirms email validity
     * @param  User $user
     * @param  string $token
     * @param  $profileService
     * @return bool
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function confirmCreate(User $user, $token, $profileService = null) {
        if($user->getConfirmationToken() != $token)
            return false;

        $user->setEnabled(true);
        $user->setConfirmationToken($this->generateToken($user));

        /*$profile = $profileService->getOneByAttributes(array('idUser' => $user->getId()));
        if(is_null($profile)) {
            return false;
        }*/

        $exception = $this->repository->enableUser($user, null);
        if(is_array($exception)) {
            return false;
        }

        if(!$this->userEmailService->create($user, $user->getEmail(), true)){
            return false;
        }

        $this->sendMailRegistrationSuccessful($user);

        return true;
    }

    /**
     * Get valid access token
     * @param User $user
     * @param string $token
     * @return bool
     */
    public function getValidToken(User $user, $token) {
        $token_list = $user->getAccessTokens()->getValues();

        /** @var AccessToken $access_token */
        foreach ($token_list as $access_token) {
            if($access_token->getToken() == $token) {
                if($access_token->hasExpired()) {
                    return false;
                }
                return true;
                break;
            }
        }
    }

    /**
     * Given the user and token returns the reset link
     * @param User $user
     * @param string $token
     * @return string
     */
    private function urlConfirmReset(User $user, $token) {
        $uris = $this->uri.'/public/user/reset/confirm';
        return $uris."/".$user->getId()."/" . $token;
    }

    /**
     * Sends the reset mail asking to confirm the password reset
     * @param User $user
     * @return User|null $user
     * @throws \Doctrine\DBAL\ConnectionException
     */
    private function sendMailResetConfirm(User $user) {
        $token = $this->confirmationToken($user);
        $url = $this->urlConfirmReset($user, $token);

        try {
            $message = \Swift_Message::newInstance()
                ->setSubject("Reset password")
                ->setFrom([$this->administration_address => $this->sender_name])
                ->setTo($user->getEmail())
                ->setContentType('text/html')
                ->setBody($this->templating->render(
                    "email/password_resetting.email.twig",
                    array(
                        'user'              => $user,
                        'confirmationUrl'   => $url
                    )
                ))
            ;

            if(!Utils::checkEmailString($user->getEmail())) {
                return null;
            }
            else {
                if(!$this->swiftMailer->send($message))
                    return null;
            }

        } catch (\Exception $e) {


            return null;
        }

        return $user;
    }

    /**
     * Start password reset procedure.
     * Returns NULL if some error occurs otherwise it returns the persisted object.
     * @param User $user
     * @return User|null
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function reset($user) {
        /** @var User $user */
        $requested = new \DateTime();
        $requested->setTimestamp(time());
        $user->setPasswordRequestedAt($requested);

        $user = $this->sendMailResetConfirm($user);
        if(is_null($user))
            return null;

        if(!$this->save($user))
            return null;

        return $user;
    }

    /**
     * Sends the reset mail that confirm successful password reset
     * @param User $user
     * @return bool
     * @throws \Doctrine\DBAL\ConnectionException
     */
    private function sendMailResetSuccessful(User $user) {
        try {
            $message = \Swift_Message::newInstance()
                ->setSubject("Password resetting completed successfully")
                ->setFrom([$this->administration_address => $this->sender_name])
                ->setTo($user->getEmail())
                ->setContentType('text/html')
                ->setBody($this->templating->render(
                    "email/password_resetting_successful.email.twig",
                    array(
                        'user' => $user
                    )
                ))
            ;

            if(!Utils::checkEmailString($user->getEmail())) {
                return false;
            }
            else {
                if(!$this->swiftMailer->send($message))
                    return false;
            }

        } catch (\Exception $e) {


            return false;
        }

        return true;
    }

    /**
     * Update credentials.
     * @param User $user
     */
    public function updateCredentials(User $user) {
        $expiration = new \DateTime();
        $expiration->setTimestamp(time()+$this->timeout_credentials);
        $password = $this->encoder->encodePassword($user, $user->getPlainPassword());

        $user->setCredentialsExpired(false);
        $user->setCredentialsExpireAt($expiration);
        $user->setPassword($password);

        return;
    }

    /**
     * Given the user and relative token confirms password reset.
     * @param User $user
     * @param string $token
     * @return bool
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function confirmReset(User $user, $token) {
        if($user->getConfirmationToken() != $token)
            return false;

        $this->updateCredentials($user);
        $user->setConfirmationToken($this->generateToken($user));

        $this->sendMailResetSuccessful($user);

        return $this->save($user);
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isAdminAuthorized (User &$user) {

        if( $user->isEnabled() && (
                in_array("ROLE_ADMIN", $user->getRoles()) ||
                in_array("ROLE_SUPER_ADMIN", $user->getRoles())
            )
        ) {
            return true;
        }

        return false;
    }


    /**
     * @return array|mixed
     */
    public function getAllOldPending () {
        return $this->repository->findAllOldPending();
    }

    /**
     * Given the user, email and token returns the change email link confirm
     * @param User $user
     * @param $email
     * @param string $token
     * @return string
     */
    private function urlConfirmChangeEmail(User $user, $email, $token) {
        $uris = $this->uri.'/public/user/change-email/confirm';
        return $uris."/".$user->getId()."/".$email."/".$token;
    }

    /**
     * Sends the reset mail asking to confirm the change email
     * @param User $user
     * @param $email
     * @return User|null $user
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function sendMailChangeEmailConfirm(User $user, $email) {
        $token = $this->confirmationToken($user);
        $url = $this->urlConfirmChangeEmail($user, $email, $token);

        try {
            $message = \Swift_Message::newInstance()
                ->setSubject("Change email")
                ->setFrom([$this->administration_address => $this->sender_name])
                ->setTo($user->getEmail())
                ->setContentType('text/html')
                ->setBody($this->templating->render(
                    "email/change_email.email.twig",
                    array(
                        'user'              => $user,
                        'email'             => $email,
                        'confirmationUrl'   => $url
                    )
                ))
            ;

            if(!Utils::checkEmailString($user->getEmail())) {
                return null;
            }
            else {
                if(!$this->swiftMailer->send($message))
                    return null;
            }

        } catch (\Exception $e) {


            return null;
        }

        return $user;
    }

}