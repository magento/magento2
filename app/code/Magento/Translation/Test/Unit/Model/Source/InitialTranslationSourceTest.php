<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Test\Unit\Model\Source;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Translation\Model\ResourceModel\Translate;
use Magento\Translation\Model\ResourceModel\TranslateFactory;
use Magento\Translation\Model\Source\InitialTranslationSource;

/**
 * @covers \Magento\Translation\Model\Source\InitialTranslationSource
 * @package Magento\Translation\Test\Unit\Model\Source
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InitialTranslationSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TranslateFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $translationFactory;

    /**
     * @var Translate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $translation;

    /**
     * @var StoreManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var Store|\PHPUnit_Framework_MockObject_MockObject
     */
    private $store;

    /**
     * @var AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connection;

    /**
     * @var Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $select;

    /**
     * @var DeploymentConfig | \PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var InitialTranslationSource
     */
    private $source;

    public function setUp()
    {
        $this->translationFactory = $this->getMockBuilder(TranslateFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->translation = $this->getMockBuilder(Translate::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connection = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->source = new InitialTranslationSource(
            $this->translationFactory,
            $this->storeManager,
            $this->deploymentConfigMock
        );
    }

    public function testGet()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('isDbAvailable')
            ->willReturn(true);
        $this->translationFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->translation);
        $this->translation->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($this->connection);
        $this->connection->expects($this->once())
            ->method('select')
            ->willReturn($this->select);
        $this->translation->expects($this->once())
            ->method('getMainTable')
            ->willReturn('main_table.translate');
        $this->select->expects($this->once())
            ->method('from')
            ->with('main_table.translate', ['string', 'translate', 'store_id', 'locale'])
            ->willReturnSelf();
        $this->select->expects($this->once())
            ->method('order')
            ->with('store_id')
            ->willReturnSelf();
        $this->connection->expects($this->once())
            ->method('fetchAll')
            ->with($this->select)
            ->willReturn([
                [
                    'store_id' => 2,
                    'locale' => 'en_US',
                    'string' => 'hello',
                    'translate' => 'bonjour'
                ]
            ]);
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->with(2)
            ->willReturn($this->store);
        $this->store->expects($this->once())
            ->method('getCode')
            ->willReturn('myStore');

        $this->assertEquals(
            [
                'en_US' => [
                    'myStore' => [
                        'hello' => 'bonjour'
                    ]
                ]
            ],
            $this->source->get()
        );
    }

    public function testGetWithoutAvailableDb()
    {
        $this->deploymentConfigMock->expects($this->once())
            ->method('isDbAvailable')
            ->willReturn(false);
        $this->assertEquals([], $this->source->get());
    }
}
