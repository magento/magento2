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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
     * @deprecated
     */
    protected $paramLabels = [];

    /**
     * @var array
     */
    protected $initArguments;

    /**
     * @var FixtureConfig
     */
    private $config;

    /**
     * Constructor
     *
     * @param IndexerReindexCommand $reindexCommand
     * @param array $initArguments
     */
    public function __construct(IndexerReindexCommand $reindexCommand, $initArguments = [])
    {
        $this->initArguments = $initArguments;
        $this->reindexCommand = $reindexCommand;
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
            $type = 'Magento\Setup\Fixtures' . '\\' . $file;
            $fixture = $this->getObjectManager()->create(
                $type,
                [
                    'fixtureModel' => $this,
                ]
            );
            $this->fixtures[$fixture->getPriority()] = $fixture;
        }

        ksort($this->fixtures);
        return $this;
    }

    /**
     * Get param labels
     *
     * @return array
     * @deprecated
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
            $this->objectManager->get(\Magento\Framework\App\State::class)->setAreaCode(self::AREA_CODE);
        }

        return $this->objectManager;
    }

    /**
     *  Init Object Manager
     *
     * @param string $area
     * @return FixtureModel
     */
    public function initObjectManager($area = self::AREA_CODE)
    {
        $objectManger = $this->getObjectManager();
        $configuration = $objectManger
            ->get(\Magento\Framework\ObjectManager\ConfigLoaderInterface::class)
            ->load($area);
        $objectManger->configure($configuration);

        $diConfiguration = $this->getValue('di');
        if (file_exists($diConfiguration)) {
            $dom = new \DOMDocument();
            $dom->load($diConfiguration);

            $objectManger->configure(
                $objectManger
                    ->get(\Magento\Framework\ObjectManager\Config\Mapper\Dom::class)
                    ->convert($dom)
            );
        }

        $objectManger->get(\Magento\Framework\Config\ScopeInterface::class)
            ->setCurrentScope($area);
        return $this;
    }

    /**
     * Reset object manager
     *
     * @return \Magento\Framework\ObjectManagerInterface
     * @deprecated
     */
    public function resetObjectManager()
    {
        return $this;
    }

    /**
     * @return FixtureConfig
     */
    private function getConfig()
    {
        if (null === $this->config) {
            $this->config = $this->getObjectManager()->get(FixtureConfig::class);
        }

        return $this->config;
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
        return $this->getConfig()->loadConfig($filename);
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
        return $this->getConfig()->getValue($key, $default);
    }
}
