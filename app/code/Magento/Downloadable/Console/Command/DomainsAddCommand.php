<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Magento\Downloadable\Api\DomainManagerInterface as DomainManager;

/**
 * Class DomainsAddCommand
 *
 * Command for adding downloadable domain to the whitelist
 */
class DomainsAddCommand extends Command
{
    /**
     * Name of domains input argument
     */
    public const INPUT_KEY_DOMAINS = 'domains';

    /**
     * @var DomainManager
     */
    private $domainManager;

    /**
     * DomainsAddCommand constructor.
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
        $description = 'Add domains to the downloadable domains whitelist';

        $this->setName('downloadable:domains:add')
            ->setDescription($description)
            ->setDefinition(
                [
                    new InputArgument(
                        self::INPUT_KEY_DOMAINS,
                        InputArgument::IS_ARRAY,
                        'Domains name'
                    )
                ]
            );
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            if ($input->getArgument(self::INPUT_KEY_DOMAINS)) {
                $whitelistBefore = $this->domainManager->getDomains();
                $newDomains = $input->getArgument(self::INPUT_KEY_DOMAINS);
                $newDomains = array_filter(array_map('trim', $newDomains), 'strlen');

                $this->domainManager->addDomains($newDomains);

                foreach (array_diff($this->domainManager->getDomains(), $whitelistBefore) as $newHost) {
                    $output->writeln(
                        $newHost . ' was added to the whitelist.'
                    );
                }
            }
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }
            return;
        }
    }
}
