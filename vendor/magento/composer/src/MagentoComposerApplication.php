<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Composer;

use Composer\Console\Application;
use Composer\IO\BufferIO;
use Composer\Factory as ComposerFactory;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class MagentoComposerApplication
 *
 * This class provides ability to set composer application settings and run any composer command.
 * Also provides method to get Composer instance so you can have access composer properties lie Locker
 */
class MagentoComposerApplication
{

    const COMPOSER_WORKING_DIR = '--working-dir';

    /**
     * Path to Composer home directory
     *
     * @var string
     */
    private $composerHome;

    /**
     * Path to composer.json file
     *
     * @var string
     */
    private $composerJson;

    /**
     * Buffered output
     *
     * @var BufferedOutput
     */
    private $consoleOutput;

    /**
     * @var ConsoleArrayInputFactory
     */
    private $consoleArrayInputFactory;

    /**
     * @var Application
     */
    private $consoleApplication;

    /**
     * Constructs class
     *
     * @param string $pathToComposerHome
     * @param string $pathToComposerJson
     * @param Application $consoleApplication
     * @param ConsoleArrayInputFactory $consoleArrayInputFactory
     * @param BufferedOutput $consoleOutput
     */
    public function __construct(
        $pathToComposerHome,
        $pathToComposerJson,
        Application $consoleApplication = null,
        ConsoleArrayInputFactory $consoleArrayInputFactory = null,
        BufferedOutput $consoleOutput = null
    ) {
        $this->consoleApplication = $consoleApplication ? $consoleApplication : new Application();
        $this->consoleArrayInputFactory = $consoleArrayInputFactory ? $consoleArrayInputFactory
            : new ConsoleArrayInputFactory();
        $this->consoleOutput = $consoleOutput ? $consoleOutput : new BufferedOutput();

        $this->composerJson = $pathToComposerJson;
        $this->composerHome = $pathToComposerHome;

        putenv('COMPOSER_HOME=' . $pathToComposerHome);

        $this->consoleApplication->setAutoExit(false);
    }

    /**
     * Creates composer object
     *
     * @return \Composer\Composer
     * @throws \Exception
     */
    public function createComposer()
    {
        return ComposerFactory::create(new BufferIO(), $this->composerJson);
    }

    /**
     * Runs composer command
     *
     * @param array $commandParams
     * @param string|null $workingDir
     * @return bool
     * @throws \RuntimeException
     */
    public function runComposerCommand(array $commandParams, $workingDir = null)
    {
        $this->consoleApplication->resetComposer();

        if ($workingDir) {
            $commandParams[self::COMPOSER_WORKING_DIR] = $workingDir;
        } else {
            $commandParams[self::COMPOSER_WORKING_DIR] = dirname($this->composerJson);
        }

        $input = $this->consoleArrayInputFactory->create($commandParams);

        $exitCode = $this->consoleApplication->run($input, $this->consoleOutput);

        if ($exitCode) {
            throw new \RuntimeException(
                sprintf('Command "%s" failed: %s', $commandParams['command'], $this->consoleOutput->fetch())
            );
        }

        return $this->consoleOutput->fetch();
    }
}
