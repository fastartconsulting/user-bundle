<?php

namespace UserBundle\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UserBundle\Entity\Client;

class ClientCreateCommand extends Command{


    protected function configure(){
        $this
            ->setName('oauth:client:create')
            ->setDescription('Create a new client')
            ->addArgument('keyword', InputArgument::REQUIRED, 'Sets the client keyword')
            ->addOption('redirect-uri', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Sets redirect uri for client. Use this option multiple times to set multiple redirect URIs.')
            ->addOption('grant-type', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Sets allowed grant type for client. Use this option multiple times to set multiple grant types.')
            ->setHelp('i.e.: php bin/console oauth:client:create <keyword> 
                                --redirect-uri="http:\\example.com" 
                                --grant-type="authorization_code"
                                --grant-type="password"
                                --grant-type="refresh_token"')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output){

        $output->writeln("START");
        $output->writeln([
            'Client Creator',
            '============',
            '',
        ]);

        $clientManager = $this->getApplication()->getKernel()->getContainer()->get('fos_oauth_server.client_manager.default');
        /** @var Client $client */
        $client = $clientManager->createClient();
        $client->setKeyword($input->getArgument('keyword'));
        $client->setRedirectUris($input->getOption('redirect-uri'));
        $client->setAllowedGrantTypes($input->getOption('grant-type'));
        $clientManager->updateClient($client);

        $output->writeln(sprintf('Added a new client with keyword <info>%s</info>, public id <info>%s</info> and secret <info>%s</info>.', $client->getKeyword(), $client->getPublicId(), $client->getSecret()));
        $output->writeln("DONE");
    }
}