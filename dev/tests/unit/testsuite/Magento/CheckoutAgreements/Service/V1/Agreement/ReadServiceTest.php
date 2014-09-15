<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CheckoutAgreements\Service\V1\Agreement;

use \Magento\TestFramework\Helper\ObjectManager;
use \Magento\Store\Model\ScopeInterface;

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
            array('create'),
            array(),
            '',
            false
        );
        $this->agreementBuilderMock = $this->getMock(
            'Magento\CheckoutAgreements\Service\V1\Data\AgreementBuilder',
            array(),
            array(),
            '',
            false
        );
        $this->storeManagerMock = $this->getMock('Magento\Framework\StoreManagerInterface');
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

        $agreementData = array(
            'id' => 1,
            'name' => 'Checkout Agreement',
            'content' => 'Agreement content: <b>HTML</b>',
            'content_height' => '100px',
            'checkbox_text' => 'Checkout Agreement Checkbox Text',
            'active' => true,
            'html' => true,
        );

        $storeId = 1;
        $storeMock = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);
        $storeMock->expects($this->any())->method('getId')->will($this->returnValue($storeId));
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));

        $collectionMock = $this->objectManager->getCollectionMock(
            'Magento\CheckoutAgreements\Model\Resource\Agreement\Collection',
            array($this->getAgreementMock($agreementData))
        );
        $this->factoryMock->expects($this->once())->method('create')->will($this->returnValue($collectionMock));
        $collectionMock->expects($this->once())->method('addStoreFilter')->with($storeId);
        $collectionMock->expects($this->once())->method('addFieldToFilter')->with('is_active', 1);

        $agreementDataObject = $this->getMock(
            'Magento\CheckoutAgreements\Service\V1\Data\Agreement',
            array(),
            array(),
            '',
            false
        );
        $this->agreementBuilderMock->expects($this->once())->method('populateWithArray')->with($agreementData);
        $this->agreementBuilderMock->expects($this->once())->method('create')
            ->will($this->returnValue($agreementDataObject));

        $this->assertEquals(array($agreementDataObject), $this->service->getList());
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
            array(
                'getId', 'getName', 'getContent', 'getContentHeight', 'getCheckboxText', 'getIsActive', 'getIsHtml',
                '__wakeup', '__sleep',
            ),
            array(),
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
