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
 * Class DomainsRemoveCommand
 *
 * Command for removing downloadable domain from the whitelist
 */
class DomainsRemoveCommand extends Command
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
     * DomainsRemoveCommand constructor.
     *
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
            $domains = $input->getArgument(self::INPUT_KEY_DOMAINS);

            $this->downloadManager->validateDomains($domains);

            $whitelistBefore = $this->domainManager->getDomains();
            $removedDomains = array_filter(array_map('trim', $domains), 'strlen');

            $this->domainManager->removeDomains($removedDomains);

            foreach (array_intersect($removedDomains, $whitelistBefore) as $removedHost) {
                $output->writeln($removedHost . ' was removed from the whitelist.' . PHP_EOL);
            }

            return Cli::RETURN_SUCCESS;
        } catch (InvalidArgumentException $e) {
            return $this->downloadManager->handleInvalidArgumentException($e, $output);
        } catch (Exception $e) {
            return $this->downloadManager->handleException($e, $output);
        }
    }
}
