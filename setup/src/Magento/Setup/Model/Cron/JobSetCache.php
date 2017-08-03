<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Magento\Backend\Console\Command\AbstractCacheManageCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class \Magento\Setup\Model\Cron\JobSetCache
 *
 * @since 2.1.0
 */
class JobSetCache extends AbstractJob
{
    /**
     * @var \Magento\Backend\Console\Command\AbstractCacheSetCommand
     * @since 2.1.0
     */
    protected $command;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     * @since 2.1.0
     */
    protected $output;

    /**
     * @var Status
     * @since 2.1.0
     */
    protected $status;

    /**
     * @param \Magento\Backend\Console\Command\AbstractCacheSetCommand $command
     * @param \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param Status $status
     * @param string $name
     * @param array $params
     * @since 2.1.0
     */
    public function __construct(
        \Magento\Backend\Console\Command\AbstractCacheSetCommand $command,
        \Magento\Setup\Model\ObjectManagerProvider $objectManagerProvider,
        \Symfony\Component\Console\Output\OutputInterface $output,
        \Magento\Setup\Model\Cron\Status $status,
        $name,
        $params = []
    ) {
        $this->command = $command;
        parent::__construct($output, $status, $objectManagerProvider, $name, $params);
    }

    /**
     * Execute set cache command
     *
     * @return void
     * @since 2.1.0
     */
    public function execute()
    {
        try {
            $arguments = [];
            if ($this->getName() === 'setup:cache:enable') {
                if (!empty($this->params)) {
                    $arguments[AbstractCacheManageCommand::INPUT_KEY_TYPES] = explode(' ', $this->params[0]);
                }
                $arguments['command'] = 'cache:enable';
                $inputDefinition = [];
                if ($this->command->getDefinition()->hasArgument('command')) {
                    $inputDefinition[] = new InputArgument('command', InputArgument::REQUIRED);
                }
                if ($this->command->getDefinition()->hasArgument(AbstractCacheManageCommand::INPUT_KEY_TYPES)) {
                    $inputDefinition[] = new InputArgument(
                        AbstractCacheManageCommand::INPUT_KEY_TYPES,
                        InputArgument::REQUIRED
                    );
                }
                if (!empty($inputDefinition)) {
                    $definition = new InputDefinition($inputDefinition);
                    $this->command->setDefinition($definition);
                }
            } else {
                $arguments['command'] = 'cache:disable';
            }
            $this->command->run(new ArrayInput($arguments), $this->output);
        } catch (\Exception $e) {
            $this->status->toggleUpdateError(true);
            throw new \RuntimeException(sprintf('Could not complete %s successfully: %s', $this, $e->getMessage()));
        }
    }
}
