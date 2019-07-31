<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Console\Command;

use Magento\Framework\App\ObjectManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table as TableHelper;
use Symfony\Component\Console\Helper\TableFactory as TableHelperFactory;

/**
 * Class StoreListCommand
 *
 * Command for listing the configured stores
 */
class StoreListCommand extends Command
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TableHelperFactory
     */
    private $tableHelperFactory;

    /**
     * @inheritDoc
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        TableHelperFactory $tableHelperFactory = null
    ) {
        $this->storeManager = $storeManager;
        $this->tableHelperFactory = $tableHelperFactory ?? ObjectManager::getInstance()->get(TableHelperFactory::class);
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
            /** @var TableHelper $table */
            $table = $this->tableHelperFactory->create(['output' => $output]);
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

            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }

            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }
}
