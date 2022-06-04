<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Magento model for performance tests
 */
namespace Magento\Setup\Fixtures;

use Magento\Indexer\Console\Command\IndexerReindexCommand;
use Magento\Setup\Exception;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FixtureModel
{
    public const AREA_CODE = 'adminhtml';

    /**
     * Fixtures file name pattern
     */
    private const FIXTURE_PATTERN = '?*Fixture.php';

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
     * @var Fixture[]
     */
    protected $fixtures = [];

    /**
     * List of fixtures indexed by class names
     *
     * @var Fixture[]
     */
    private $fixturesByNames = [];

    /**
     * Parameters labels
     *
     * @var array
     * @deprecated 2.2.0
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
     * @var IndexerReindexCommand
     */
    private $reindexCommand;

    /**
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
        $files = glob(__DIR__ . DIRECTORY_SEPARATOR . self::FIXTURE_PATTERN, GLOB_NOSORT);
        foreach ($files as $file) {
            $file = basename($file, '.php');
            /** @var Fixture $fixture */
            $type = 'Magento\Setup\Fixtures' . '\\' . $file;
            $fixture = $this->getObjectManager()->create(
                $type,
                [
                    'fixtureModel' => $this,
                ]
            );
            $this->loadFixture($fixture);
        }
        foreach ($this->getFixturesFromRegistry() as $fixture) {
            $this->loadFixture($fixture);
        }
        ksort($this->fixtures);
        return $this;
    }

    /**
     * Gets Fixtures from FixtureRegistry and gets instances of them from ObjectManager
     *
     * @return array
     */
    private function getFixturesFromRegistry() : array
    {
        $fixtureRegistry = $this->getObjectManager()->create(FixtureRegistry::class);
        $fixtures = [];
        foreach ($fixtureRegistry->getFixtures() as $fixtureClassName) {
            $fixtures[] = $this->getObjectManager()->create(
                $fixtureClassName,
                ['fixtureModel' => $this]
            );
        }
        return $fixtures;
    }

    /**
     * Loads fixture into $this->fixturesByName and $this->fixtures
     *
     * @param Fixture $fixture
     * @return void
     */
    private function loadFixture(Fixture $fixture)
    {
        $fixtureClassName = get_class($fixture);
        if (isset($this->fixtures[$fixture->getPriority()])) {
            throw new \InvalidArgumentException(
                sprintf('Duplicate priority %d in fixture %s', $fixture->getPriority(), $fixtureClassName)
            );
        }
        if ($fixture->getPriority() >= 0) {
            $this->fixtures[$fixture->getPriority()] = $fixture;
        }
        $this->fixturesByNames[$fixtureClassName] = $fixture;
    }

    /**
     * Get param labels
     *
     * @return array
     * @deprecated 2.2.0
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
     * Returns fixture by name
     *
     * @param string $name
     * @return Fixture
     * @throws \Magento\Setup\Exception
     */
    public function getFixtureByName($name)
    {
        if (!array_key_exists($name, $this->fixturesByNames)) {
            throw new Exception('Wrong fixture name');
        }

        return $this->fixturesByNames[$name];
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
     * @deprecated 2.2.0
     */
    public function resetObjectManager()
    {
        return $this;
    }

    /**
     * Gets instance of FixtureConfig from ObjectManager
     *
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
