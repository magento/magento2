<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Cron;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class FrontendActionsFlushTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Catalog\Cron\FrontendActionsFlush */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Catalog\Model\ResourceModel\ProductFrontendAction|\PHPUnit_Framework_MockObject_MockObject */
    protected $productFrontendActionMock;

    /** @var \Magento\Catalog\Model\FrontendStorageConfigurationPool|\PHPUnit_Framework_MockObject_MockObject */
    protected $frontendStorageConfigurationPoolMock;

    protected function setUp()
    {
        $this->productFrontendActionMock = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\ProductFrontendAction::class
        )
                ->disableOriginalConstructor()
                ->getMock();
        $this->frontendStorageConfigurationPoolMock = $this->getMockBuilder(
            \Magento\Catalog\Model\FrontendStorageConfigurationPool::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Cron\FrontendActionsFlush::class,
            [
                'productFrontendActionResource' => $this->productFrontendActionMock,
                'frontendStorageConfigurationPool' => $this->frontendStorageConfigurationPoolMock
            ]
        );
    }

    public function testExecute()
    {
        $connectionMock = $this->getMock(\Magento\Framework\DB\Adapter\AdapterInterface::class, [], [], '', false);
        $selectMock = $this->getMock(\Magento\Framework\DB\Select::class, [], [], '', false);
        $frontendConfiguration = $this->getMock(\Magento\Catalog\Model\FrontendStorageConfigurationInterface::class);

        $selectMock
            ->expects($this->once())
            ->method('from')
            ->with('catalog_product_frontend_action', ['action_id', 'type_id'])
            ->will($this->returnSelf());
        $selectMock
            ->expects($this->once())
            ->method('group')
            ->with('type_id')
            ->willReturnSelf();

        $frontendConfiguration->expects($this->once())
            ->method('get')
            ->willReturn([
                'lifetime' => 1500
            ]);

        $this->frontendStorageConfigurationPoolMock->expects($this->once())
            ->method('get')
            ->with('recently_viewed_product')
            ->willReturn($frontendConfiguration);
        $this->productFrontendActionMock->expects($this->exactly(2))
            ->method('getMainTable')
            ->willReturn('catalog_product_frontend_action');
        $this->productFrontendActionMock->expects($this->exactly(2))
            ->method('getConnection')
            ->willReturn($connectionMock);
        $connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($selectMock);
        $connectionMock->expects($this->once())
            ->method('fetchPairs')
            ->with($selectMock)
            ->willReturn([
                'recently_viewed_product'
            ]);

        $connectionMock->expects($this->once())
            ->method('quoteInto')
            ->with('added_at < ?', time() - 1500)
            ->willReturn(['added_at < ?', time() - 1500]);
        $connectionMock->expects($this->once())
            ->method('delete')
            ->with('catalog_product_frontend_action', [['added_at < ?', time() - 1500]]);

        $this->model->execute();
    }
}
