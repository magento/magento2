<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\SearchAdapter;

use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;
use Magento\AdvancedSearch\Model\Client\ClientFactoryInterface;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Psr\Log\LoggerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

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
    protected $logger;

    /**
     * @var ClientFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
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
    protected function setUp()
    {
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientFactory = $this->getMockBuilder(\Magento\AdvancedSearch\Model\Client\ClientFactoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->clientConfig = $this->getMockBuilder(ClientOptionsInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->clientConfig->expects($this->any())
            ->method('prepareClientOptions')
            ->willReturn([
                'hostname' => 'localhost',
                'port' => '9200',
                'timeout' => 15,
                'enableAuth' => 1,
                'username' => 'user',
                'password' => 'passwd',
            ]);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\Elasticsearch\SearchAdapter\ConnectionManager::class,
            [
                'clientFactory' => $this->clientFactory,
                'clientConfig' => $this->clientConfig,
                'logger' => $this->logger
            ]
        );
    }

    /**
     * Test getConnection() method without errors
     */
    public function testGetConnectionSuccessfull()
    {
        $client = $this->getMockBuilder(\Magento\Elasticsearch\Model\Client\Elasticsearch::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientFactory->expects($this->once())
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
