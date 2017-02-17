<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Magento\Setup\Console\Command\AbstractSetupCommand;
use Magento\Setup\Model\ObjectManagerProvider;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Job that handles module commands. E.g. "module:enable", "module:disable"
 */
class JobModule extends AbstractJob
{
    /**
     * @var string $cmdString
     */
    protected $cmdString;

    /**
     * Constructor
     *
     * @param AbstractSetupCommand $command
     * @param ObjectManagerProvider $objectManagerProvider
     * @param OutputInterface $output
     * @param Status $status
     * @param string $name
     * @param array $params
     */
    public function __construct(
        AbstractSetupCommand $command,
        ObjectManagerProvider $objectManagerProvider,
        OutputInterface $output,
        Status $status,
        $name,
        $params = []
    ) {
        $this->command = $command;
        parent::__construct($output, $status, $objectManagerProvider, $name, $params);

        // map name to command string
        $this->setCommandString($name);
    }

    /**
     * Sets up the command to be run through bin/magento
     *
     * @param string $name
     * @return void
     */
    private function setCommandString($name)
    {
        if ($name == 'setup:module:enable') {
            $this->cmdString = 'module:enable';
        } else {
            $this->cmdString = 'module:disable';
        }
    }

    /**
     * Execute job
     *
     * @throws \RuntimeException
     * @return void
     */
    public function execute()
    {
        try {
            foreach ($this->params['components'] as $compObj) {
                if (isset($compObj['name']) && (!empty($compObj['name']))) {
                    $moduleNames[] = $compObj['name'];
                } else {
                    throw new \RuntimeException('component name is not set.');
                }
            }

            // prepare the arguments to invoke Symfony run()
            $arguments['command'] = $this->cmdString;
            $arguments['module'] = $moduleNames;

            $statusCode = $this->command->run(new ArrayInput($arguments), $this->output);

            // check for return statusCode to catch any Symfony errors
            if ($statusCode != 0) {
                throw new \RuntimeException('Symfony run() returned StatusCode: ' . $statusCode);
            }

            //perform the generated file cleanup
            $this->performCleanup();

        } catch (\Exception $e) {
            $this->status->toggleUpdateError(true);
            throw new \RuntimeException(
                sprintf('Could not complete %s successfully: %s', $this->cmdString, $e->getMessage())
            );
        }
    }
}
