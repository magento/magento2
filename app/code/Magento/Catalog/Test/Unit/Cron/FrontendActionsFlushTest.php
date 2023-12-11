<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Cron;

use Magento\Catalog\Cron\FrontendActionsFlush;
use Magento\Catalog\Model\FrontendStorageConfigurationInterface;
use Magento\Catalog\Model\FrontendStorageConfigurationPool;
use Magento\Catalog\Model\ResourceModel\ProductFrontendAction;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FrontendActionsFlushTest extends TestCase
{
    /** @var FrontendActionsFlush */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var ProductFrontendAction|MockObject */
    protected $productFrontendActionMock;

    /** @var FrontendStorageConfigurationPool|MockObject */
    protected $frontendStorageConfigurationPoolMock;

    protected function setUp(): void
    {
        $this->productFrontendActionMock = $this->getMockBuilder(
            ProductFrontendAction::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->frontendStorageConfigurationPoolMock = $this->getMockBuilder(
            FrontendStorageConfigurationPool::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            FrontendActionsFlush::class,
            [
                'productFrontendActionResource' => $this->productFrontendActionMock,
                'frontendStorageConfigurationPool' => $this->frontendStorageConfigurationPoolMock
            ]
        );
    }

    public function testExecute()
    {
        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $selectMock = $this->createMock(Select::class);
        $frontendConfiguration = $this->getMockForAbstractClass(FrontendStorageConfigurationInterface::class);

        $selectMock
            ->expects($this->once())
            ->method('from')
            ->with('catalog_product_frontend_action', ['action_id', 'type_id'])->willReturnSelf();
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
