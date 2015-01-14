<?php
/**
 * Test class for \Magento\Sales\Block\Adminhtml\Order\Create\Form\Account
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
     * @var \Magento\TestFramework\Helper\Bootstrap
     */
    protected $_objectManager;

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
            ['getCustomerId', 'getStore', 'getStoreId', 'getQuote']
        )->getMock();
        $sessionQuoteMock->expects($this->any())->method('getCustomerId')->will($this->returnValue(1));
        $sessionQuoteMock->expects($this->any())->method('getQuote')->will($this->returnValue($quote));
        /** @var \Magento\Framework\View\LayoutInterface $layout */
        $layout = $this->_objectManager->get('Magento\Framework\View\LayoutInterface');
        $this->_accountBlock = $layout->createBlock(
            'Magento\Sales\Block\Adminhtml\Order\Create\Form\Account',
            'address_block' . rand(),
            ['sessionQuote' => $sessionQuoteMock]
        );
        parent::setUp();
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetForm()
    {
        $expectedFields = ['group_id', 'email'];
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
