<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CheckoutAgreements\Test\Unit\Block;

use Magento\CheckoutAgreements\Block\Agreements;
use Magento\CheckoutAgreements\Model\AgreementsProvider;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Collection;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AgreementsTest extends TestCase
{
    /**
     * @var Agreements
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $agreementCollFactoryMock;

    /**
     * @var MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->agreementCollFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );

        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->once())->method('getScopeConfig')->willReturn($this->scopeConfigMock);
        $contextMock->expects($this->once())->method('getStoreManager')->willReturn($this->storeManagerMock);

        $this->model = $objectManager->getObject(
            Agreements::class,
            [
                'agreementCollectionFactory' => $this->agreementCollFactoryMock,
                'context' => $contextMock
            ]
        );
    }

    public function testGetAgreements()
    {
        $storeId = 100;
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(AgreementsProvider::PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);

        $agreementCollection = $this->createMock(
            Collection::class
        );
        $this->agreementCollFactoryMock->expects($this->once())->method('create')->willReturn($agreementCollection);

        $storeMock = $this->createMock(Store::class);
        $storeMock->expects($this->once())->method('getId')->willReturn($storeId);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);

        $agreementCollection->expects($this->once())->method('addStoreFilter')->with($storeId)->willReturnSelf();
        $agreementCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('is_active', 1)
            ->willReturnSelf();

        $this->assertEquals($agreementCollection, $this->model->getAgreements());
    }

    public function testGetAgreementsIfAgreementsDisabled()
    {
        $expectedResult = [];
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(AgreementsProvider::PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn(false);
        $this->assertEquals($expectedResult, $this->model->getAgreements());
    }
}
