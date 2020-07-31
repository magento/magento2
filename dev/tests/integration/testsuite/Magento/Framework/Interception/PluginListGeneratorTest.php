<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\TestFramework\Application;
use Magento\TestFramework\Helper\Bootstrap;

class PluginListGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Generated plugin list config for frontend scope
     */
    const CACHE_ID = 'primary|global|frontend|plugin-list';

    /**
     * @var PluginListGenerator
     */
    private $model;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var DriverInterface
     */
    private $file;

    /**
     * @var Application
     */
    private $application;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->application = Bootstrap::getInstance()->getBootstrap()->getApplication();
        $this->directoryList = new DirectoryList(BP, $this->getCustomDirs());
        $this->file = Bootstrap::getObjectManager()->create(DriverInterface::class);
        $reader = Bootstrap::getObjectManager()->create(
        // phpstan:ignore "Class Magento\Framework\ObjectManager\Config\Reader\Dom\Proxy not found."
            \Magento\Framework\ObjectManager\Config\Reader\Dom\Proxy::class
        );
        $scopeConfig = Bootstrap::getObjectManager()->create(\Magento\Framework\Config\Scope::class);
        $omConfig = Bootstrap::getObjectManager()->create(
            \Magento\Framework\Interception\ObjectManager\Config\Developer::class
        );
        $relations = Bootstrap::getObjectManager()->create(
            \Magento\Framework\ObjectManager\Relations\Runtime::class
        );
        $definitions = Bootstrap::getObjectManager()->create(
            \Magento\Framework\Interception\Definition\Runtime::class
        );
        $classDefinitions = Bootstrap::getObjectManager()->create(
            \Magento\Framework\ObjectManager\Definition\Runtime::class
        );
        // phpstan:ignore "Class Psr\Log\LoggerInterface\Proxy not found."
        $logger = Bootstrap::getObjectManager()->create(\Psr\Log\LoggerInterface\Proxy::class);
        $this->model = new PluginListGenerator(
            $reader,
            $scopeConfig,
            $omConfig,
            $relations,
            $definitions,
            $classDefinitions,
            $logger,
            $this->directoryList,
            ['primary', 'global']
        );
    }

    /**
     * Test plugin list configuration generation and load.
     */
    public function testPluginListConfigGeneration()
    {
        $scopes = ['frontend'];
        $this->model->write($scopes);
        $configData = $this->model->load(self::CACHE_ID);
        $this->assertNotEmpty($configData[0]);
        $this->assertNotEmpty($configData[1]);
        $this->assertNotEmpty($configData[2]);
        $expected = [
            1 => [
                0 => 'genericHeaderPlugin',
                1 => 'asyncCssLoad',
                2 => 'response-http-page-cache'
            ]
        ];
        // Here in test is assumed that this class below has 3 plugins. But the amount of plugins and class itself
        // may vary. If it is changed, please update these assertions.
        $this->assertArrayHasKey(
            'Magento\\Framework\\App\\Response\\Http_sendResponse___self',
            $configData[2],
            'Processed plugin does not exist in the processed plugins array.'
        );
        $this->assertSame(
            $expected,
            $configData[2]['Magento\\Framework\\App\\Response\\Http_sendResponse___self'],
            'Plugin configurations are not equal'
        );
    }

    /**
     * Gets customized directory paths
     *
     * @return array
     */
    private function getCustomDirs()
    {
        $path = DirectoryList::PATH;
        $generated = "{$this->application->getTempDir()}/generated";

        return [
            DirectoryList::GENERATED_METADATA => [$path => "{$generated}/metadata"],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $filePath = $this->directoryList->getPath(DirectoryList::GENERATED_METADATA)
            . '/' . self::CACHE_ID . '.' . 'php';

        if (file_exists($filePath)) {
            $this->file->deleteFile($filePath);
        }
    }
}
