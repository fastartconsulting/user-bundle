<?php

namespace FAC\UserBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use FAC\UserBundle\Entity\User;
use FAC\UserBundle\Service\UserService;

class UserResetPasswordCommand extends ContainerAwareCommand  {

    /** @var UserService $userService */
    private $userService;

    /**
     * UserResetPasswordCommand constructor.
     * @param UserService $userService
     */
    public function __construct(UserService $userService) {
        $this->userService    = $userService;

        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('user:reset-password')
            ->setDescription('Allow to reset a user password')
            ->addArgument('id_user', InputArgument::REQUIRED, 'User id')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $id_user = (int)$input->getArgument('id_user');

        /** @var User $user */
        $user = $this->userService->getOneB($id_user);

        if(is_null($user)) {
            $output->writeln('The user does not exist');
        } else {

            $plain_password = "TempPass".rand(1000,5000);
            $user->setPlainPassword($plain_password);
            $expiration = new \DateTime();
            $expiration->setTimestamp(time()+$this->userService->timeout_credentials);
            $password = $this->userService->getEncodedPassword($user);
            $user->setCredentialsExpired(false);
            $user->setCredentialsExpireAt($expiration);
            $user->setPassword($password);
            $token = $this->userService->confirmationToken($user);
            $user->setConfirmationToken($token);

            try {
                if(!$this->userService->save($user)) {
                    $output->writeln('Error in saving operation');
                } else {
                    $output->writeln('The new password is '. $plain_password);
                }
            } catch (\Exception $e) {
                $output->writeln('Error in saving operation');
            }

        }

    }


}