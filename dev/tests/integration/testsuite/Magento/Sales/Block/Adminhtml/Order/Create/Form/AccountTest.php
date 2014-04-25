<?php
/**
 * Test class for \Magento\Sales\Block\Adminhtml\Order\Create\Form\Account
 *
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
namespace Magento\Sales\Block\Adminhtml\Order\Create\Form;

/**
 * @magentoAppArea adminhtml
 */
class AccountTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Sales\Block\Adminhtml\Order\Create\Form\Account */
    protected $_accountBlock;

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     */
    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $quote = $this->_objectManager->create('Magento\Sales\Model\Quote')->load(1);
        $sessionQuoteMock = $this->getMockBuilder(
            'Magento\Backend\Model\Session\Quote'
        )->disableOriginalConstructor()->setMethods(
            array('getCustomerId', 'getStore', 'getStoreId', 'getQuote')
        )->getMock();
        $sessionQuoteMock->expects($this->any())->method('getCustomerId')->will($this->returnValue(1));
        $sessionQuoteMock->expects($this->any())->method('getQuote')->will($this->returnValue($quote));
        /** @var \Magento\Framework\View\LayoutInterface $layout */
        $layout = $this->_objectManager->get('Magento\Framework\View\LayoutInterface');
        $this->_accountBlock = $layout->createBlock(
            'Magento\Sales\Block\Adminhtml\Order\Create\Form\Account',
            'address_block' . rand(),
            array('sessionQuote' => $sessionQuoteMock)
        );
        parent::setUp();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetForm()
    {
        $expectedFields = array('group_id', 'email');
        $form = $this->_accountBlock->getForm();
        $this->assertEquals(1, $form->getElements()->count(), "Form has invalid number of fieldsets");
        $fieldset = $form->getElements()[0];

        $this->assertEquals(count($expectedFields), $fieldset->getElements()->count());

        foreach ($fieldset->getElements() as $element) {
            $this->assertTrue(
                in_array($element->getId(), $expectedFields),
                sprintf('Unexpected field "%s" in form.', $element->getId())
            );
        }
    }
}
