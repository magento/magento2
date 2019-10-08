<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Magento\Downloadable\Api\DomainManagerInterface as DomainManager;

/**
 * Command for listing allowed downloadable domains.
 */
class DomainsShowCommand extends Command
{
    /**
     * @var DomainManager
     */
    private $domainManager;

    /**
     * @param DomainManager $domainManager
     */
    public function __construct(
        DomainManager $domainManager
    ) {
        $this->domainManager = $domainManager;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $description = 'Display downloadable domains whitelist';

        $this->setName('downloadable:domains:show')
            ->setDescription($description);
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $whitelist = implode("\n", $this->domainManager->getDomains());
            $output->writeln(
                "Downloadable domains whitelist:\n$whitelist"
            );
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }
        }
    }
}
