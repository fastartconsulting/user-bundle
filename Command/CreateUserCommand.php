<?php

namespace FAC\UserBundle\Command;


use ResourceBundle\Entity\CalendarTimezone;
use ResourceBundle\Service\CalendarTimezoneService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use FAC\UserBundle\Entity\Client;
use FAC\UserBundle\Entity\User;
use FAC\UserBundle\Service\UserService;
use Utils\Utils;

class CreateUserCommand extends Command{

    /** @var UserService $userService */
    private $userService;

    /** @var CalendarTimezoneService $calendarTimezoneService */
    private $calendarTimezoneService;

    /**
     * CreateUserCommand constructor.
     * @param UserService $userService
     * @param CalendarTimezoneService $calendarTimezoneService
     */
    public function __construct(UserService $userService,
                                CalendarTimezoneService $calendarTimezoneService
    ) {

        $this->userService               = $userService;
        $this->calendarTimezoneService   = $calendarTimezoneService;

        parent::__construct();
    }

    protected function configure(){
        $this
            ->setName('user:custom:create')
            ->setDescription('Create a new user')
            ->addArgument('email', InputArgument::REQUIRED, 'Set user email')
            ->addArgument('password', InputArgument::REQUIRED, 'Set user password')
            ->addArgument('client-keyword', InputArgument::REQUIRED, 'Set the client keyword')
            ->addArgument('id-timezone', InputArgument::REQUIRED, 'id of timezone')
            ->addOption('role', null, InputOption::VALUE_REQUIRED, 'Set a user role.')
            ->setHelp('i.e.: php bin/console user:custom:create <email> <password> <client-keyword> <id-timezone>
                                --role="ROLE_SUPER_ADMIN"')
        ;
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Doctrine\DBAL\ConnectionException
     */
    protected function execute(InputInterface $input, OutputInterface $output){

        $output->writeln("START");

        $email      = $input->getArgument('email');
        $password   = $input->getArgument('password');
        $keyword    = $input->getArgument('client-keyword');
        $idTimezone = $input->getArgument('id-timezone');
        $role       = $input->getOption('role');

        /** @var CalendarTimezone $calendarTimezone */
        $calendarTimezone = $this->calendarTimezoneService->getById($idTimezone);

        if(is_null($calendarTimezone)) {
            $output->writeln("CALENDAR TIMEZONE IS NULL");
        } else {

            $randomString = Utils::randomString(5);

            /** @var User $user */
            $user = $this->userService->init(
                array(
                    'first_name'    => "Fn".$randomString,
                    'last_name'     => "Ln".$randomString,
                    'email'         => $email,
                    'plainPassword' => $password
                ));

            if(!is_null($role) && $role != "ROLE_USER") {
                $user->addRole($role);
            }

            $user->setEnabled(true);

            $user = $this->userService->create($user,$calendarTimezone,$keyword);
            if(is_null($user)) {
                $output->writeln("ERROR - USER NOT CREATED");
            }
        }

        $output->writeln("DONE");
    }
}