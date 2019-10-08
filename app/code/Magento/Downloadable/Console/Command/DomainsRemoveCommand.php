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
use Symfony\Component\Console\Input\InputArgument;
use Magento\Downloadable\Api\DomainManagerInterface as DomainManager;

/**
 * Command for removing downloadable domain from the whitelist.
 */
class DomainsRemoveCommand extends Command
{
    /**
     * Name of domains input argument.
     */
    const INPUT_KEY_DOMAINS = 'domains';

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
        $description = 'Remove domains from the downloadable domains whitelist';

        $this->setName('downloadable:domains:remove')
            ->setDescription($description)
            ->setDefinition(
                [
                    new InputArgument(
                        self::INPUT_KEY_DOMAINS,
                        InputArgument::IS_ARRAY,
                        'Domain names'
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
                $removeDomains = $input->getArgument(self::INPUT_KEY_DOMAINS);
                $removeDomains = array_filter(array_map('trim', $removeDomains), 'strlen');
                $this->domainManager->removeDomains($removeDomains);
                foreach (array_diff($whitelistBefore, $this->domainManager->getDomains()) as $removedHost) {
                    $output->writeln(
                        $removedHost . ' was removed from the whitelist.'
                    );
                }
            }
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }
        }
    }
}
