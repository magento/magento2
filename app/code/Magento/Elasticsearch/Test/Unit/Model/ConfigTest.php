<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model;

use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\Elasticsearch\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Elasticsearch config model tests.
 */
class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    protected $model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var ClientResolver|MockObject
     */
    private $clientResolverMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManager;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->clientResolverMock = $this->createMock(ClientResolver::class);

        $this->objectManager = new ObjectManagerHelper($this);
        $this->model = $this->objectManager->getObject(
            Config::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'clientResolver' => $this->clientResolverMock,
            ]
        );
    }

    /**
     * Test prepareClientOptions() method
     */
    public function testPrepareClientOptions()
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturn('');
        $options = [
            'hostname' => 'localhost',
            'port' => '9200',
            'index' => 'magento2',
            'enableAuth' => '1',
            'username' => 'user',
            'password' => 'pass',
            'timeout' => 1,
        ];
        $this->assertEquals($options, $this->model->prepareClientOptions(array_merge($options, ['test' => 'test'])));
    }

    /**
     * Test getIndexPrefix() method
     */
    public function testGetIndexPrefix()
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->willReturn('indexPrefix');
        $this->assertEquals('indexPrefix', $this->model->getIndexPrefix());
    }

    /**
     * Test getEntityType() method
     */
    public function testGetEntityType()
    {
        $this->assertIsString($this->model->getEntityType());
    }

    /**
     * Test getEntityType() method
     */
    public function testIsElasticsearchEnabled()
    {
        $this->assertFalse($this->model->isElasticsearchEnabled());
    }

    /**
     * Test retrieve search engine configuration information.
     *
     * @return void
     */
    public function testGetElasticsearchConfigData(): void
    {
        $fieldName = 'server_hostname';

        $this->clientResolverMock->expects($this->once())
            ->method('getCurrentEngine')
            ->willReturn(Config::ENGINE_NAME);

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('catalog/search/' . Config::ENGINE_NAME . '_' . $fieldName, ScopeInterface::SCOPE_STORE, 1);

        $this->model->getElasticsearchConfigData($fieldName, 1);
    }

    /**
     * Test retrieve search engine configuration information with predefined prefix.
     *
     * @return void
     */
    public function testGetElasticsearchConfigDataWithPredefinedPrefix(): void
    {
        $fieldName = 'server_hostname';
        $model = $this->objectManager->getObject(
            Config::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'clientResolver' => $this->clientResolverMock,
                'prefix' => Config::ENGINE_NAME,
            ]
        );

        $this->clientResolverMock->expects($this->never())
            ->method('getCurrentEngine');

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('catalog/search/' . Config::ENGINE_NAME . '_' . $fieldName, ScopeInterface::SCOPE_STORE, 1);

        $model->getElasticsearchConfigData($fieldName, 1);
    }
}
