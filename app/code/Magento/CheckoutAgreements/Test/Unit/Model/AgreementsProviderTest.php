<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CheckoutAgreements\Test\Unit\Model;

use Magento\CheckoutAgreements\Model\AgreementModeOptions;
use Magento\CheckoutAgreements\Model\AgreementsProvider;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Collection;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AgreementsProviderTest extends TestCase
{
    /**
     * @var AgreementsProvider
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var MockObject
     */
    protected $agreementCollFactoryMock;

    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->agreementCollFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->model = $objectManager->getObject(
            AgreementsProvider::class,
            [
                'agreementCollectionFactory' => $this->agreementCollFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetRequiredAgreementIdsIfAgreementsEnabled(): void
    {
        $storeId = 100;
        $expectedResult = [1, 2, 3, 4, 5];
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
        $agreementCollection
            ->method('addFieldToFilter')
            ->willReturnCallback(function ($arg1, $arg2) use ($agreementCollection) {
                if ($arg1 == 'is_active' && $arg2 == 1) {
                    return $agreementCollection;
                } elseif ($arg1 == 'mode' && $arg2 == AgreementModeOptions::MODE_MANUAL) {
                    return $agreementCollection;
                }
            });

        $agreementCollection->expects($this->once())->method('getAllIds')->willReturn($expectedResult);

        $this->assertEquals($expectedResult, $this->model->getRequiredAgreementIds());
    }

    /**
     * @return void
     */
    public function testGetRequiredAgreementIdsIfAgreementsDisabled(): void
    {
        $expectedResult = [];
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(AgreementsProvider::PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn(false);
        $this->assertEquals($expectedResult, $this->model->getRequiredAgreementIds());
    }
}
