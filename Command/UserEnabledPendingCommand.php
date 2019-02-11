<?php

namespace FAC\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use FAC\UserBundle\Entity\User;
use FAC\UserBundle\Service\UserService;

class UserEnabledPendingCommand extends ContainerAwareCommand  {

    /** @var UserService $userService */
    private $userService;

    /**
     * UserEnabledPendingCommand constructor.
     * @param UserService $userService
     */
    public function __construct(UserService $userService
    ) {
        $this->userService    = $userService;

        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('user:remove-pending')
            ->setDescription('Check all user if not enabled from long time.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $output->writeln("START");
        $list_users = $this->userService->getAllOldPending();

        if(count($list_users) < 1) {
            $output->writeln("NO USER DISABLED FOUND");
        } else {

//            $errors = 0;
//            $removed = 0;

            $userIds = array();

            /** @var User $user */
            foreach($list_users as $user) {

                try {

                    $isRemoved = $this->userService->doRemove($user);
                    if (is_array($isRemoved)) {
                        $this->log_monitor->trace(LogMonitor::LOG_CHANNEL_ERROR, 500, "remove.error", $isRemoved);
                    } else {
                        $userIds[] = $user->getId();
                    }

                } catch (\Exception $e) {
                    $output->writeln("EXCEPTION FOUNDED");
                    $exception = LogUtils::getFormattedExceptions($e);
                    $this->log_monitor->trace(LogMonitor::LOG_CHANNEL_ERROR, 500, "remove.error", $exception);
                }

            }

            $isFlushed = $this->userService->doFlush();
            if (is_array($isFlushed)) {
                $this->log_monitor->trace(LogMonitor::LOG_CHANNEL_ERROR, 500, "remove.error", $isFlushed);
            } else {
                if (count($userIds) > 0) {
                    //$profiles = $this->profileService->getAllByUserIds($userIds);
                    $profiles = array();
                    /*if (!empty($profiles) && count($profiles)>0) {
                        foreach ($profiles as $profile) {
                            $this->profileService->forceDelete($profile);
                        }
                    }*/
                }
            }

        }

        $output->writeln("DONE");

    }


}