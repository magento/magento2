<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Controller\Adminhtml\Email;

/**
 * @magentoAppArea adminhtml
 */
class TemplateTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    public function testDefaultTemplateAction()
    {
        /** @var $formKey \Magento\Framework\Data\Form\FormKey */
        $formKey = $this->_objectManager->get('Magento\Framework\Data\Form\FormKey');
        $post = [
            'form_key' => $formKey->getFormKey(),
            'code' => 'customer_password_forgot_email_template',
        ];
        $this->getRequest()->setPostValue($post);
        $this->dispatch('backend/admin/email_template/defaultTemplate/?isAjax=true');
        $this->assertContains(
            '"template_type":2,"template_subject":"Reset your',
            $this->getResponse()->getBody()
        );
    }
}
