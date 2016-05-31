<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\Unit\Block;

use Magento\Store\Model\ScopeInterface;
use Magento\CheckoutAgreements\Model\AgreementsProvider;

class AgreementsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CheckoutAgreements\Block\Agreements
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $agreementCollFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

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

        $this->scopeConfigMock = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');
        $this->storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface');

        $contextMock = $this->getMock('\Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $contextMock->expects($this->once())->method('getScopeConfig')->willReturn($this->scopeConfigMock);
        $contextMock->expects($this->once())->method('getStoreManager')->willReturn($this->storeManagerMock);

        $this->model = $objectManager->getObject(
            'Magento\CheckoutAgreements\Block\Agreements',
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
