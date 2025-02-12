<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Developer\Console\Command;

use InvalidArgumentException;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TemplateHintsDisableCommand extends Command
{
    public const COMMAND_NAME = 'dev:template-hints:disable';

    public const SUCCESS_MESSAGE = "Template hints disabled. Refresh cache types";

    /**
     * @var ConfigInterface
     */
    private $resourceConfig;

    /**
     * Initialize dependencies.
     *
     * @param ConfigInterface $resourceConfig
     */
    public function __construct(ConfigInterface $resourceConfig)
    {
        parent::__construct();
        $this->resourceConfig = $resourceConfig;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Disable frontend template hints. A cache flush might be required.');

        parent::configure();
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->resourceConfig->saveConfig('dev/debug/template_hints_storefront', 0, 'default', 0);
        $output->writeln("<info>". self::SUCCESS_MESSAGE . "</info>");

        return Cli::RETURN_SUCCESS;
    }
}
