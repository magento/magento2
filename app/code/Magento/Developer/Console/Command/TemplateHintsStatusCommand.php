<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Developer\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\Console\Cli;

/**
 * Command to show frontend template hints status
 */
class TemplateHintsStatusCommand extends Command
{
    
    const COMMAND_NAME = 'dev:template-hints:status';
    const TEMPLATE_HINTS_STOREFRONT_PATH = 'dev/debug/template_hints_storefront';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var ReinitableConfigInterface
     */
    private $reinitableConfig;

    /**
     * Initialize dependencies.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ReinitableConfigInterface $reinitableConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ReinitableConfigInterface $reinitableConfig
    )
    {
        parent::__construct();
        $this->scopeConfig = $scopeConfig;
        $this->reinitableConfig = $reinitableConfig;
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
        $this->reinitableConfig->reinit();
        $templateHintsStatus =
            $this->scopeConfig->isSetFlag(self::TEMPLATE_HINTS_STOREFRONT_PATH, 'default')
                ? 'enabled'
                : 'disabled';
        $templateHintsMessage = __("Template hints are %status", ['status' => $templateHintsStatus]);
        $output->writeln("<info>" . $templateHintsMessage . "</info>");

        return Cli::RETURN_SUCCESS;
    }
}
