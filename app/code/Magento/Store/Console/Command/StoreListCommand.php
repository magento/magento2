<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Console\Command;

use Exception;
use Magento\Framework\Console\Cli;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

/**
 * Class StoreListCommand
 *
 * Command for listing the configured stores
 */
class StoreListCommand extends Command
{
    public function __construct(
        private readonly StoreManagerInterface $storeManager
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('store:list')
            ->setDescription('Displays the list of stores');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $table = new Table($output);
            $table->setHeaders(['ID', 'Website ID', 'Group ID', 'Name', 'Code', 'Sort Order', 'Is Active']);

            foreach ($this->storeManager->getStores(true, true) as $store) {
                $table->addRow([
                    $store->getId(),
                    $store->getWebsiteId(),
                    $store->getStoreGroupId(),
                    $store->getName(),
                    $store->getCode(),
                    $store->getData('sort_order'),
                    $store->getData('is_active'),
                ]);
            }

            $table->render();

            return Cli::RETURN_SUCCESS;
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }

            return Cli::RETURN_FAILURE;
        }
    }
}
