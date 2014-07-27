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
namespace Magento\Customer\Block\Account\Dashboard;

class HelloTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The Hello block.
     *
     * @var Hello
     */
    private $block;

    /**
     * Session model.
     *
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * Execute per test initialization.
     */
    public function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');

        $this->customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $this->block = $objectManager->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Customer\Block\Account\Dashboard\Hello',
            '',
            array('customerSession' => $this->customerSession)
        )->setTemplate(
            'account/dashboard/hello.phtml'
        );
    }

    /**
     * Execute per test post cleanup.
     */
    public function tearDown()
    {
        $this->customerSession->unsCustomerId();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetCustomerName()
    {
        $this->customerSession->setCustomerId(1);
        $this->assertEquals('Firstname Lastname', $this->block->getCustomerName());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testToHtml()
    {
        $this->customerSession->setCustomerId(1);
        $html = $this->block->toHtml();
        $this->assertContains("<div class=\"block block-dashboard-welcome\">", $html);
        $this->assertContains("<strong>Hello, Firstname Lastname!</strong>", $html);
    }
}
