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
 * Class WebsiteListCommand
 *
 * Command for listing the configured websites
 */
class WebsiteListCommand extends Command
{
    /**
     * @var \Magento\Store\Api\WebsiteManagementInterface
     */
    private $manager;

    /**
     * @var TableHelperFactory
     */
    private $tableHelperFactory;

    /**
     * @inheritDoc
     */
    public function __construct(
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteManagement,
        TableHelperFactory $tableHelperFactory = null
    ) {
        $this->manager = $websiteManagement;
        $this->tableHelperFactory = $tableHelperFactory ?? ObjectManager::getInstance()->get(TableHelperFactory::class);
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('store:website:list')
            ->setDescription('Displays the list of websites');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $table = $this->tableHelperFactory->create(['output' => $output]);
            $table->setHeaders(['ID', 'Default Group Id', 'Name', 'Code', 'Sort Order', 'Is Default']);

            foreach ($this->manager->getList() as $website) {
                $table->addRow([
                    $website->getId(),
                    $website->getDefaultGroupId(),
                    $website->getName(),
                    $website->getCode(),
                    $website->getData('sort_order'),
                    $website->getData('is_default'),
                ]);
            }

            $table->render($output);

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
