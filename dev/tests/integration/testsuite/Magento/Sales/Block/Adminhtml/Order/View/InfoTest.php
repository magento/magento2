<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\View;

/**
 * Test class for \Magento\Sales\Block\Adminhtml\Order\View\Info
 */
class InfoTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Value for the user defined custom attribute, which is created by attribute_user_defined_customer.php fixture.
     */
    const ORDER_USER_DEFINED_ATTRIBUTE_VALUE = 'custom_attr_value';

    public function testCustomerGridAction()
    {
        $layout = $this->_objectManager->get('Magento\Framework\View\LayoutInterface');
        /** @var \Magento\Sales\Block\Adminhtml\Order\View\Info $infoBlock */
        $infoBlock = $layout->createBlock(
            'Magento\Sales\Block\Adminhtml\Order\View\Info',
            'info_block' . mt_rand(),
            []
        );

        $result = $infoBlock->getCustomerAccountData();
        $this->assertEquals([], $result, 'Customer has additional account data.');
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testGetCustomerGroupName()
    {
        $layout = $this->_objectManager->get('Magento\Framework\View\LayoutInterface');
        /** @var \Magento\Sales\Block\Adminhtml\Order\View\Info $customerGroupBlock */
        $customerGroupBlock = $layout->createBlock(
            'Magento\Sales\Block\Adminhtml\Order\View\Info',
            'info_block' . mt_rand(),
            ['registry' => $this->_putOrderIntoRegistry()]
        );

        $result = $customerGroupBlock->getCustomerGroupName();
        $this->assertEquals('NOT LOGGED IN', $result);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoDataFixture Magento/Customer/_files/attribute_user_defined_customer.php
     */
    public function testGetCustomerAccountData()
    {
        $layout = $this->_objectManager->get('Magento\Framework\View\LayoutInterface');

        $orderData = [
            'customer_' . FIXTURE_ATTRIBUTE_USER_DEFINED_CUSTOMER_NAME => self::ORDER_USER_DEFINED_ATTRIBUTE_VALUE,
        ];
        /** @var \Magento\Sales\Block\Adminhtml\Order\View\Info $customerGroupBlock */
        $customerGroupBlock = $layout->createBlock(
            'Magento\Sales\Block\Adminhtml\Order\View\Info',
            'info_block' . mt_rand(),
            ['registry' => $this->_putOrderIntoRegistry($orderData)]
        );

        $this->assertEquals(
            [
                200 => [
                    'label' => FIXTURE_ATTRIBUTE_USER_DEFINED_CUSTOMER_FRONTEND_LABEL,
                    'value' => self::ORDER_USER_DEFINED_ATTRIBUTE_VALUE,
                ],
            ],
            $customerGroupBlock->getCustomerAccountData()
        );
    }

    /**
     * @param array $additionalOrderData
     * @return \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _putOrderIntoRegistry(array $additionalOrderData = [])
    {
        $registry = $this->getMockBuilder('Magento\Framework\Registry')->disableOriginalConstructor()->getMock();

        $order = $this->_objectManager->get(
            'Magento\Sales\Model\Order'
        )->load(
            '100000001'
        )->setData(
            array_merge(['customer_group_id' => 0], $additionalOrderData)
        );

        $registry->expects($this->any())->method('registry')->with('current_order')->will($this->returnValue($order));

        return $registry;
    }
}
