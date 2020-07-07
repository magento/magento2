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

class PluginListGeneratorMultipleScopesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Generated plugin list configs for frontend, adminhtml, graphql scopes
     */
    private $cacheIds = [
        'primary|global|frontend|plugin-list',
        'primary|global|adminhtml|plugin-list',
        'primary|global|graphql|plugin-list'
    ];

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
     * Test multiple scopes plugin list configuration generation and load.
     */
    public function testPluginListMultipleScopesConfigGeneration()
    {
        $scopes = ['frontend', 'adminhtml', 'graphql'];
        $this->model->write($scopes);

        foreach ($this->cacheIds as $cacheId) {
            $configData = $this->model->load($cacheId);
            $this->assertNotEmpty($configData[0]);
            $this->assertNotEmpty($configData[1]);
            $this->assertNotEmpty($configData[2]);
        }
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
        foreach ($this->cacheIds as $cacheId) {
            $filePath = $this->directoryList->getPath(DirectoryList::GENERATED_METADATA)
                . '/' . $cacheId . '.' . 'php';

            if (file_exists($filePath)) {
                $this->file->deleteFile($filePath);
            }
        }
    }
}
