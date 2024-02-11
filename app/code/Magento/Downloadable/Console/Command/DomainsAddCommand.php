<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Console\Command;

use Exception;
use InvalidArgumentException;
use Magento\Downloadable\Api\DomainManagerInterface as DomainManager;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Downloadable\Helper\Download as DownloadManager;

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
     * @var DownloadManager
     */
    private $downloadManager;

    /**
     * DomainsAddCommand constructor.
     * @param DomainManager $domainManager
     * @param DownloadManager $downloadManager
     */
    public function __construct(
        DomainManager   $domainManager,
        DownloadManager $downloadManager
    ) {
        $this->domainManager = $domainManager;
        $this->downloadManager = $downloadManager;
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
            $domains = $input->getArgument(self::INPUT_KEY_DOMAINS);

            $this->downloadManager->validateDomains($domains);

            $whitelistBefore = $this->domainManager->getDomains();
            $newDomains = array_filter(array_map('trim', $domains), 'strlen');

            $this->domainManager->addDomains($newDomains);

            foreach (array_diff($this->domainManager->getDomains(), $whitelistBefore) as $newHost) {
                $output->writeln($newHost . ' was added to the whitelist.' . PHP_EOL);
            }

            return Cli::RETURN_SUCCESS;
        } catch (InvalidArgumentException $e) {
            return $this->downloadManager->handleInvalidArgumentException($e, $output);
        } catch (Exception $e) {
            return $this->downloadManager->handleException($e, $output);
        }
    }
}
