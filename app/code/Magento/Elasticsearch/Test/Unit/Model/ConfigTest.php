<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model;

use Magento\Elasticsearch\Model\Config;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class ConfigTest
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config
     */
    protected $model;

    /**
     * @var ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfig;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            \Magento\Elasticsearch\Model\Config::class,
            [
                'scopeConfig' => $this->scopeConfig
            ]
        );
    }

    /**
     * Test prepareClientOptions() method
     */
    public function testPrepareClientOptions()
    {
        $this->scopeConfig->expects($this->any())
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
        $this->scopeConfig->expects($this->any())
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
}
