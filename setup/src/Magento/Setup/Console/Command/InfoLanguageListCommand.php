<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Magento\Framework\Setup\Lists;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command prints list of available language locales
 * @since 2.0.0
 */
class InfoLanguageListCommand extends Command
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
        $this->setName('info:language:list')
            ->setDescription('Displays the list of available language locales');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = $this->getHelperSet()->get('table');
        $table->setHeaders(['Language', 'Code']);

        foreach ($this->lists->getLocaleList() as $key => $locale) {
            $table->addRow([$locale, $key]);
        }

        $table->render($output);
    }
}
