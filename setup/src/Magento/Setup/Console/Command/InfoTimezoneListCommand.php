<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\Setup\Lists;

/**
 * Command prints list of available timezones
 * @since 2.0.0
 */
class InfoTimezoneListCommand extends Command
{
    /**
     * List model provides lists of available options for currency, language locales, timezones
     *
     * @var Lists
     * @since 2.0.0
     */
    private $lists;

    /**
     * @param Lists $lists
     * @since 2.0.0
     */
    public function __construct(Lists $lists)
    {
        $this->lists = $lists;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function configure()
    {
        $this->setName('info:timezone:list')
            ->setDescription('Displays the list of available timezones');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = $this->getHelperSet()->get('table');
        $table->setHeaders(['Timezone', 'Code']);

        foreach ($this->lists->getTimezoneList() as $key => $timezone) {
            $table->addRow([$timezone, $key]);
        }

        $table->render($output);
    }
}
