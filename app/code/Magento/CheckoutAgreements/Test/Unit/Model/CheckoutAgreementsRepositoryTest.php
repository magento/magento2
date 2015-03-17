<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\Unit\Model;

use Magento\CheckoutAgreements\Model\CheckoutAgreementsRepository;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CheckoutAgreementsRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CheckoutAgreementsRepository
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $factoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->factoryMock = $this->getMock(
            'Magento\CheckoutAgreements\Model\Resource\Agreement\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->storeManagerMock = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->model = new \Magento\CheckoutAgreements\Model\CheckoutAgreementsRepository(
            $this->factoryMock,
            $this->storeManagerMock,
            $this->scopeConfigMock
        );
    }

    public function testGetListReturnsEmptyListIfCheckoutAgreementsAreDisabledOnFrontend()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with('checkout/options/enable_agreements', ScopeInterface::SCOPE_STORE, null)
            ->will($this->returnValue(false));
        $this->factoryMock->expects($this->never())->method('create');
        $this->assertEmpty($this->model->getList());
    }

    public function testGetListReturnsTheListOfActiveCheckoutAgreements()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with('checkout/options/enable_agreements', ScopeInterface::SCOPE_STORE, null)
            ->will($this->returnValue(true));

        $agreementDataObject = $this->getMock(
            'Magento\CheckoutAgreements\Model\Agreement',
            [],
            [],
            '',
            false
        );

        $storeId = 1;
        $storeMock = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $storeMock->expects($this->any())->method('getId')->will($this->returnValue($storeId));
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));

        $collectionMock = $this->objectManager->getCollectionMock(
            'Magento\CheckoutAgreements\Model\Resource\Agreement\Collection',
            [$agreementDataObject]
        );
        $this->factoryMock->expects($this->once())->method('create')->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->once())->method('addStoreFilter')->with($storeId);
        $collectionMock->expects($this->once())->method('addFieldToFilter')->with('is_active', 1);

        $this->assertEquals([$agreementDataObject], $this->model->getList());
    }
}
