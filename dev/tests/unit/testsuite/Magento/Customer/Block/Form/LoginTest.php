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
namespace Magento\Customer\Block\Form;

class LoginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Customer\Block\Form\Login
     */
    protected $block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Helper\Data
     */
    protected $customerHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Checkout\Helper\Data
     */
    protected $checkoutData;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Core\Helper\Url
     */
    protected $coreUrl;

    public function setUp()
    {
        $this->customerHelper = $this->getMockBuilder(
            'Magento\Customer\Helper\Data'
        )->disableOriginalConstructor()->setMethods(
            array('getRegisterUrl')
        )->getMock();
        $this->checkoutData = $this->getMockBuilder(
            'Magento\Checkout\Helper\Data'
        )->disableOriginalConstructor()->setMethods(
            array('isContextCheckout')
        )->getMock();
        $this->coreUrl = $this->getMockBuilder(
            'Magento\Core\Helper\Url'
        )->disableOriginalConstructor()->setMethods(
            array('addRequestParam')
        )->getMock();

        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->block = $this->objectManager->getObject(
            'Magento\Customer\Block\Form\Login',
            array(
                'customerHelper' => $this->customerHelper,
                'checkoutData' => $this->checkoutData,
                'coreUrl' => $this->coreUrl
            )
        );
    }

    public function testGetCreateAccountUrl()
    {
        $expectedUrl = 'Custom Url';

        $this->block->setCreateAccountUrl($expectedUrl);
        $this->checkoutData->expects($this->any())->method('isContextCheckout')->will($this->returnValue(false));
        $this->assertEquals($expectedUrl, $this->block->getCreateAccountUrl());

        $this->checkoutData->expects($this->any())->method('isContextCheckout')->will($this->returnValue(true));
        $this->coreUrl->expects(
            $this->any()
        )->method(
            'addRequestParam'
        )->with(
            $expectedUrl,
            array('context' => 'checkout')
        )->will(
            $this->returnValue($expectedUrl)
        );
        $this->assertEquals($expectedUrl, $this->block->getCreateAccountUrl());

        $this->block->unsCreateAccountUrl();
        $this->customerHelper->expects($this->any())->method('getRegisterUrl')->will($this->returnValue($expectedUrl));
        $this->checkoutData->expects($this->any())->method('isContextCheckout')->will($this->returnValue(false));
        $this->assertEquals($expectedUrl, $this->block->getCreateAccountUrl());

        $this->customerHelper->expects($this->any())->method('getRegisterUrl')->will($this->returnValue($expectedUrl));
        $this->checkoutData->expects($this->any())->method('isContextCheckout')->will($this->returnValue(true));
        $this->coreUrl->expects(
            $this->any()
        )->method(
            'addRequestParam'
        )->with(
            $expectedUrl,
            array('context' => 'checkout')
        )->will(
            $this->returnValue($expectedUrl)
        );
        $this->assertEquals($expectedUrl, $this->block->getCreateAccountUrl());
    }
}
