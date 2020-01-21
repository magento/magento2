<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Command to show frontend template hints status
 */
class TemplateHintsStatusCommand extends Command
{

    /**
     * command name
     */
    const COMMAND_NAME = 'dev:template-hints:status';

    /**
     * Success message
     */
    const SUCCESS_MESSAGE = "Template hints are %status";

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Initialize dependencies.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        parent::__construct();
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Show frontend template hints status.');

        parent::configure();
    }

    /**
     * @inheritdoc
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $templateHintsStatus =
            ($this->scopeConfig->isSetFlag('dev/debug/template_hints_storefront', 'default'))
                ? 'enabled'
                : 'disabled';
        $templateHintsMessage = __(self::SUCCESS_MESSAGE, ['status' => $templateHintsStatus]);
        $output->writeln("<info>" . $templateHintsMessage . "</info>");

        return;
    }
}
