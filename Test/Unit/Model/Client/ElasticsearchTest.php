<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Client;

use Magento\Elasticsearch\Model\Client\Elasticsearch as ElasticsearchClient;

class ElasticsearchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ElasticsearchClient
     */
    protected $model;

    /**
     * @var \Elasticsearch\Client|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $elasticsearchClientMock;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
        $this->elasticsearchClientMock = $this->getMockBuilder('\Elasticsearch\Client')
            ->setMethods(['ping', 'indices'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new ElasticsearchClient($this->getOptions(), $this->elasticsearchClientMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testConstructorOptionsException()
    {
        new ElasticsearchClient([]);
    }

    /**
     * Test client creation from the list of options
     */
    public function testConstructorWithOptions()
    {
        new ElasticsearchClient($this->getOptions());
    }

    /**
     * Test ping functionality
     */
    public function testPing()
    {
        $this->elasticsearchClientMock->expects($this->once())->method('ping')->willReturn(true);
        $this->assertEquals(true, $this->model->ping());
    }

    /**
     * Test validation of connection parameters
     */
    public function testTestConnection()
    {
        $indicesMock = $this->getMock('\Elasticsearch\Namespaces\IndicesNamespace', ['exists'], [], '', false);
        $this->elasticsearchClientMock->expects($this->once())->method('indices')->willReturn($indicesMock);
        $indicesMock->expects($this->once())->method('exists')->willReturn(true);
        $this->assertEquals(true, $this->model->validateConnectionParameters());
    }

    /**
     * Get elasticsearch client options
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            'hostname' => 'localhost',
            'port' => '9200',
            'timeout' => 15,
            'index' => 'magento2',
            'enableAuth' => 1,
            'username' => 'user',
            'password' => 'passwd',
        ];
    }
}
