<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\Unit\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\CheckoutAgreements\Model\AgreementsProvider;
use Magento\CheckoutAgreements\Model\AgreementModeOptions;

class AgreementsProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CheckoutAgreements\Model\AgreementsProvider
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $agreementCollFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->agreementCollFactoryMock = $this->getMock(
            '\Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $this->scopeConfigMock = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');

        $this->model = $objectManager->getObject(
            'Magento\CheckoutAgreements\Model\AgreementsProvider',
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

        $agreementCollection = $this->getMock(
            '\Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Collection',
            [],
            [],
            '',
            false
        );
        $this->agreementCollFactoryMock->expects($this->once())->method('create')->willReturn($agreementCollection);

        $storeMock = $this->getMock('\Magento\Store\Model\Store', [], [], '', false);
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
