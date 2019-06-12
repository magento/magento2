<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Symfony\Component\Console\Helper\TableFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\Setup\Lists;
use Magento\Framework\App\ObjectManager;

/**
 * Command prints list of available currencies
 */
class InfoCurrencyListCommand extends Command
{
    /**
     * List model provides lists of available options for currency, language locales, timezones
     *
     * @var Lists
     */
    private $lists;

    /**
     * @var TableFactory
     */
    private $tableHelperFactory;

    /**
     * @param Lists $lists
     * @param TableFactory $tableHelperFactory
     */
    public function __construct(Lists $lists, TableFactory $tableHelperFactory = null)
    {
        $this->lists = $lists;
        $this->tableHelperFactory = $tableHelperFactory ?: ObjectManager::getInstance()->create(TableFactory::class);
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('info:currency:list')
            ->setDescription('Displays the list of available currencies');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tableHelper = $this->tableHelperFactory->create(['output' => $output]);
        $tableHelper->setHeaders(['Currency', 'Code']);

        foreach ($this->lists->getCurrencyList() as $key => $currency) {
            $tableHelper->addRow([$currency, $key]);
        }

<<<<<<< HEAD
        $table->render($output);
=======
        $tableHelper->render();
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
