<?php
namespace Magento\NewRelicReporting\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use Magento\NewRelicReporting\Model\Apm\DeploymentsFactory;
use Magento\NewRelicReporting\Model\ServiceShellUser;

class DeployMarker extends Command
{    
    protected $deploymentsFactory;
    protected $serviceShellUser;
    public function __construct(
        DeploymentsFactory $deploymentsFactory,
        ServiceShellUser $serviceShellUser,        
        $name = null
    )
    {
        $this->deploymentsFactory = $deploymentsFactory;
        $this->serviceShellUser = $serviceShellUser;
        parent::__construct($name);
    }
        
    protected function configure()
    {
        $this->setName("newrelic:create:deploy-marker");
        $this->setDescription("Check the deploy queue for entries and create an appropriate deploy marker.")
              ->addArgument(
                'message',
                InputArgument::REQUIRED,
                'Deploy Message?')
              ->addArgument(
                'changelog',
                InputArgument::REQUIRED,
                'Change Log?')
              ->addArgument(
                'user',
                InputArgument::OPTIONAL,
                'Deployment User');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->deploymentsFactory->create()->setDeployment(
            $input->getArgument('message'),
            $input->getArgument('changelog'),
            $this->serviceShellUser->get($input->getArgument('user'))
        );    
    }
} 