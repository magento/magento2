<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter;

use Magento\AdvancedSearch\Model\Client\ClientFactoryInterface;
use Magento\AdvancedSearch\Model\Client\ClientInterface;
use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 *  Test for Magento\Elasticsearch\SearchAdapter\ConnectionManager
 */
class ConnectionManagerTest extends TestCase
{
    /**
     * @var ConnectionManager
     */
    protected $model;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $logger;

    /**
     * @var ClientFactoryInterface|MockObject
     */
    private $clientFactory;

    /**
     * @var ClientOptionsInterface|MockObject
     */
    private $clientConfig;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->clientFactory = $this->getMockBuilder(ClientFactoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->clientConfig = $this->getMockBuilder(ClientOptionsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

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
            ConnectionManager::class,
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
        $client = $this->getMockBuilder(ClientInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->clientFactory->expects($this->once())
            ->method('create')
            ->willReturn($client);

        $this->model->getConnection();
    }

    /**
     * Test getConnection() method with errors
     */
    public function testGetConnectionFailure()
    {
        $this->expectException(\RuntimeException::class);

        $this->clientFactory->expects($this->any())
            ->method('create')
            ->willThrowException(new \Exception('Something went wrong'));
        $this->model->getConnection();
    }
}
