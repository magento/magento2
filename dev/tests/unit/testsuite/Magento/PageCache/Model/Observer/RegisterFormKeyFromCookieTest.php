<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\Observer;

class RegisterFormKeyFromCookieTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\PageCache\Model\Observer\RegisterFormKeyFromCookie */
    protected $_model;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\PageCache\FormKey */
    protected $_formKey;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Session\Generic */
    protected $_session;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Escaper */
    protected $_escaper;

    /**
     * Set up all mocks and data for test
     */
    public function setUp()
    {
        $this->_formKey = $this->getMock('Magento\Framework\App\PageCache\FormKey', [], [], '', false);
        $this->_session = $this->getMock('Magento\Framework\Session\Generic', ['setData'], [], '', false);
        $this->_escaper = $this->getMock('\Magento\Framework\Escaper', ['escapeHtml'], [], '', false);

        $this->_model = new \Magento\PageCache\Model\Observer\RegisterFormKeyFromCookie(
            $this->_formKey,
            $this->_session,
            $this->_escaper
        );
    }

    public function testExecute()
    {
        //Data
        $formKey = '<asdfaswqrwqe12>';
        $escapedFormKey = 'asdfaswqrwqe12';

        //Verification
        $this->_formKey->expects($this->once())
            ->method('get')
            ->will($this->returnValue($formKey));

        $this->_escaper->expects($this->once())
            ->method('escapeHtml')
            ->with($formKey)
            ->will($this->returnValue($escapedFormKey));

        $this->_session->expects($this->once())
            ->method('setData')
            ->with(\Magento\Framework\Data\Form\FormKey::FORM_KEY, $escapedFormKey);

        $this->_model->execute();
    }
}
