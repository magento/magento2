<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\SearchAdapter;

use Magento\AdvancedSearch\Model\ClientOptionsInterface;
use Magento\AdvancedSearch\Model\Client\FactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;

/**
 * Class ConnectionManagerTest
 */
class ConnectionManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConnectionManager
     */
    protected $model;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var FactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $clientFactory;

    /**
     * @var ClientOptionsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $clientConfig;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
        $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientFactory = $this->getMockBuilder('\Magento\AdvancedSearch\Model\Client\FactoryInterface')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->clientConfig = $this->getMockBuilder(ClientOptionsInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'prepareClientOptions'
                ]
            )
            ->getMock();
        $this->clientConfig->expects($this->any())
            ->method('prepareClientOptions')
            ->willReturn([]);
        $this->model = new ConnectionManager(
            $this->clientFactory,
            $this->clientConfig,
            $this->logger
        );
    }

    /**
     * Test getConnection() method without errors
     */
    public function testGetConnectionSuccessfull()
    {
        $client = $this->getMockBuilder('\Magento\Elasticsearch\Model\Client\Elasticsearch')
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientFactory->expects($this->any())
            ->method('create')
            ->willReturn($client);
        $this->model->getConnection();
    }

    /**
     * Test getConnection() method with errors
     * @expectedException \RuntimeException
     */
    public function testGetConnectionFailure()
    {
        $this->clientFactory->expects($this->any())
            ->method('create')
            ->willThrowException(new \Exception('Something went wrong'));
        $this->model->getConnection();
    }
}
