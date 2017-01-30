<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Magento model for performance tests
 */
namespace Magento\Setup\Fixtures;

use Magento\Indexer\Console\Command\IndexerReindexCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Xml\Parser;

class FixtureModel
{
    /**
     * Area code
     */
    const AREA_CODE = 'adminhtml';

    /**
     * Fixtures file name pattern
     */
    const FIXTURE_PATTERN = '?*Fixture.php';

    /**
     * Application object
     *
     * @var \Magento\Framework\AppInterface
     */
    protected $application;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * List of fixtures applied to the application
     *
     * @var \Magento\Setup\Fixtures\Fixture[]
     */
    protected $fixtures = [];

    /**
     * Parameters labels
     *
     * @var array
     */
    protected $paramLabels = [];

    /**
     * @var array
     */
    protected $initArguments;

    /**
     * Configuration array
     *
     * @var array
     */
    protected $config = [];

    /**
     * XML file parser
     *
     * @var Parser
     */
    protected $fileParser;

    /**
     * Constructor
     *
     * @param IndexerReindexCommand $reindexCommand
     * @param Parser $fileParser
     * @param array $initArguments
     */
    public function __construct(IndexerReindexCommand $reindexCommand, Parser $fileParser, $initArguments = [])
    {
        $this->initArguments = $initArguments;
        $this->reindexCommand = $reindexCommand;
        $this->fileParser = $fileParser;
    }

    /**
     * Run reindex
     *
     * @param OutputInterface $output
     * @return void
     */
    public function reindex(OutputInterface $output)
    {
        $input = new ArrayInput([]);
        $this->reindexCommand->run($input, $output);
    }

    /**
     * Load fixtures
     *
     * @return $this
     * @throws \Exception
     */
    public function loadFixtures()
    {
        $files = glob(__DIR__ . DIRECTORY_SEPARATOR . self::FIXTURE_PATTERN);

        foreach ($files as $file) {
            $file = basename($file, '.php');
            /** @var \Magento\Setup\Fixtures\Fixture $fixture */
            $fixture = $this->objectManager->create(
                'Magento\Setup\Fixtures' . '\\' . $file,
                [
                    'fixtureModel' => $this
                ]
            );
            $this->fixtures[$fixture->getPriority()] = $fixture;
        }

        ksort($this->fixtures);
        foreach ($this->fixtures as $fixture) {
            $this->paramLabels = array_merge($this->paramLabels, $fixture->introduceParamLabels());
        }
        return $this;
    }

    /**
     * Get param labels
     *
     * @return array
     */
    public function getParamLabels()
    {
        return $this->paramLabels;
    }

    /**
     * Get fixtures
     *
     * @return Fixture[]
     */
    public function getFixtures()
    {
        return $this->fixtures;
    }

    /**
     * Get object manager
     *
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public function getObjectManager()
    {
        if (!$this->objectManager) {
            $objectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(
                BP,
                $this->initArguments
            );
            $this->objectManager = $objectManagerFactory->create($this->initArguments);
            $this->objectManager->get('Magento\Framework\App\State')->setAreaCode(self::AREA_CODE);
        }
        return $this->objectManager;
    }

    /**
     * Init Object Manager
     *
     * @return FixtureModel
     */
    public function initObjectManager()
    {
        $this->getObjectManager()
            ->configure(
                $this->getObjectManager()
                    ->get('Magento\Framework\ObjectManager\ConfigLoaderInterface')
                    ->load(self::AREA_CODE)
            );
        $this->getObjectManager()->get('Magento\Framework\Config\ScopeInterface')->setCurrentScope(self::AREA_CODE);
        return $this;
    }

    /**
     * Reset object manager
     *
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public function resetObjectManager()
    {
        $this->objectManager = null;
        $this->initObjectManager();
        return $this;
    }

    /**
     * Load config from file
     *
     * @param string $filename
     * @throws \Exception
     *
     * @return void
     */
    public function loadConfig($filename)
    {
        if (!is_readable($filename)) {
            throw new \Exception("Profile configuration file `{$filename}` is not readable or does not exists.");
        }
        $this->config = $this->fileParser->load($filename)->xmlToArray();
    }

    /**
     * Get profile configuration value
     *
     * @param string $key
     * @param null|mixed $default
     *
     * @return mixed
     */
    public function getValue($key, $default = null)
    {
        return isset($this->config['config']['profile'][$key]) ? $this->config['config']['profile'][$key] : $default;
    }
}
