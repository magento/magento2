<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\Unit\Model;

use Magento\CheckoutAgreements\Model\AgreementModeOptions;
use Magento\CheckoutAgreements\Model\AgreementsProvider;
use Magento\Store\Model\ScopeInterface;

class AgreementsProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CheckoutAgreements\Model\AgreementsProvider
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $agreementCollFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerMock;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->agreementCollFactoryMock = $this->createPartialMock(
            \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory::class,
            ['create']
        );
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->model = $objectManager->getObject(
            \Magento\CheckoutAgreements\Model\AgreementsProvider::class,
            [
                'agreementCollectionFactory' => $this->agreementCollFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    public function testGetRequiredAgreementIdsIfAgreementsEnabled()
    {
        $storeId = 100;
        $expectedResult = [1, 2, 3, 4, 5];
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(AgreementsProvider::PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);

        $agreementCollection = $this->createMock(
            \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Collection::class
        );
        $this->agreementCollFactoryMock->expects($this->once())->method('create')->willReturn($agreementCollection);

        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $storeMock->expects($this->once())->method('getId')->willReturn($storeId);
        $this->storeManagerMock->expects($this->once())->method('getStore')->willReturn($storeMock);

        $agreementCollection->expects($this->once())->method('addStoreFilter')->with($storeId)->willReturnSelf();
        $agreementCollection->expects($this->at(1))
            ->method('addFieldToFilter')
            ->with('is_active', 1)
            ->willReturnSelf();
        $agreementCollection->expects($this->at(2))
            ->method('addFieldToFilter')
            ->with('mode', AgreementModeOptions::MODE_MANUAL)
            ->willReturnSelf();
        $agreementCollection->expects($this->once())->method('getAllIds')->willReturn($expectedResult);

        $this->assertEquals($expectedResult, $this->model->getRequiredAgreementIds());
    }

    public function testGetRequiredAgreementIdsIfAgreementsDisabled()
    {
        $expectedResult = [];
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(AgreementsProvider::PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn(false);
        $this->assertEquals($expectedResult, $this->model->getRequiredAgreementIds());
    }
}
