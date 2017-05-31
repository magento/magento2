<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class TemplateHintsEnableCommand extends Command
{

    /**
     * command name
     */
    const COMMAND_NAME = 'dev:template-hints:enable';

    /**
     * Success message
     */
    const SUCCESS_MESSAGE = "Template hints enabled.";

    /**
     * TemplateHintsDisableCommand constructor.
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     */
    public function __construct(
        \Magento\Config\Model\ResourceModel\Config $resourceConfig
    ) {
        parent::__construct();
        $this->_resourceConfig = $resourceConfig;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Disable frontend template hints. A cache flush might be required.');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_resourceConfig->saveConfig(
                    'dev/debug/template_hints_storefront',
                    1,
                    'default',
                    0
                );

        $output->writeln("<info>". self::SUCCESS_MESSAGE . "</info>");
    }
}
