<?php

namespace FAC\UserBundle\Security;

use FAC\UserBundle\Security\UserProvider;
use FAC\UserBundle\Entity\User;

class EmailUserProvider extends UserProvider {

    /**
     * {@inheritdoc}
     */
    protected function findUser($username) {

//        return $this->userManager->findUserByUsernameOrEmail($username);

        $user = $this->userManager->findUserByUsernameOrEmail($username);

        if (!is_null($user) && $user instanceof User) {
            if(!$user->isEnabled() || $user->isLocked()) {
                return null;
            }
        }

        return $user;
    }

}
