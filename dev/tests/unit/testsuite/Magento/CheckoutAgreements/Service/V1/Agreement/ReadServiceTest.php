<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Service\V1\Agreement;

use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\ObjectManager;

class ReadServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReadService
     */
    private $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $factoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $agreementBuilderMock;

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
        $this->agreementBuilderMock = $this->getMock(
            'Magento\CheckoutAgreements\Service\V1\Data\AgreementBuilder',
            [],
            [],
            '',
            false
        );
        $this->storeManagerMock = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->service = new ReadService(
            $this->factoryMock,
            $this->agreementBuilderMock,
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
        $this->assertEmpty($this->service->getList());
    }

    public function testGetListReturnsTheListOfActiveCheckoutAgreements()
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with('checkout/options/enable_agreements', ScopeInterface::SCOPE_STORE, null)
            ->will($this->returnValue(true));

        $agreementData = [
            'id' => 1,
            'name' => 'Checkout Agreement',
            'content' => 'Agreement content: <b>HTML</b>',
            'content_height' => '100px',
            'checkbox_text' => 'Checkout Agreement Checkbox Text',
            'active' => true,
            'html' => true,
        ];

        $storeId = 1;
        $storeMock = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $storeMock->expects($this->any())->method('getId')->will($this->returnValue($storeId));
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));

        $collectionMock = $this->objectManager->getCollectionMock(
            'Magento\CheckoutAgreements\Model\Resource\Agreement\Collection',
            [$this->getAgreementMock($agreementData)]
        );
        $this->factoryMock->expects($this->once())->method('create')->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->once())->method('addStoreFilter')->with($storeId);
        $collectionMock->expects($this->once())->method('addFieldToFilter')->with('is_active', 1);

        $agreementDataObject = $this->getMock(
            'Magento\CheckoutAgreements\Service\V1\Data\Agreement',
            [],
            [],
            '',
            false
        );
        $this->agreementBuilderMock->expects($this->once())->method('populateWithArray')->with($agreementData);
        $this->agreementBuilderMock->expects($this->once())->method('create')
            ->will($this->returnValue($agreementDataObject));

        $this->assertEquals([$agreementDataObject], $this->service->getList());
    }

    /**
     * Retrieve agreement mock based on given data
     *
     * @param array $agreementData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAgreementMock(array $agreementData)
    {
        $agreementMock = $this->getMock(
            'Magento\CheckoutAgreements\Model\Agreement',
            [
                'getId', 'getName', 'getContent', 'getContentHeight', 'getCheckboxText', 'getIsActive', 'getIsHtml',
                '__wakeup', '__sleep',
            ],
            [],
            '',
            false
        );
        $agreementMock->expects($this->any())->method('getId')
            ->will($this->returnValue($agreementData['id']));
        $agreementMock->expects($this->any())->method('getName')
            ->will($this->returnValue($agreementData['name']));
        $agreementMock->expects($this->any())->method('getContent')
            ->will($this->returnValue($agreementData['content']));
        $agreementMock->expects($this->any())->method('getContentHeight')
            ->will($this->returnValue($agreementData['content_height']));
        $agreementMock->expects($this->any())->method('getCheckboxText')
            ->will($this->returnValue($agreementData['checkbox_text']));
        $agreementMock->expects($this->any())->method('getIsActive')
            ->will($this->returnValue($agreementData['active']));
        $agreementMock->expects($this->any())->method('getIsHtml')
            ->will($this->returnValue($agreementData['html']));
        return $agreementMock;
    }
}
