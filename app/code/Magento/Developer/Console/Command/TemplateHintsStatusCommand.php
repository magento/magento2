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
use Magento\Framework\Console\Cli;

/**
 * Command to show frontend template hints status
 */
class TemplateHintsStatusCommand extends Command
{
    
    const COMMAND_NAME = 'dev:template-hints:status';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

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
            ($this->isTemplateHintsEnabled())
                ? 'enabled'
                : 'disabled';
        $templateHintsMessage = __("Template hints are %status", ['status' => $templateHintsStatus]);
        $output->writeln("<info>" . $templateHintsMessage . "</info>");

        return Cli::RETURN_SUCCESS;
    }

    /**
     * @return bool
     */
    private function isTemplateHintsEnabled()
    {
        return ($this->scopeConfig->isSetFlag('dev/debug/template_hints_storefront', 'default'));
    }
}
