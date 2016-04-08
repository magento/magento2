<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Observer;

use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test captcha observer behavior
 *
 * @magentoAppArea adminhtml
 */
class CaseBackendLoginActionWithInvalidCaptchaReturnsErrorTest extends AbstractController
{
    /**
     * @magentoAdminConfigFixture admin/captcha/forms backend_login
     * @magentoAdminConfigFixture admin/captcha/enable 1
     * @magentoAdminConfigFixture admin/captcha/mode always
     */
    public function testBackendLoginActionWithInvalidCaptchaReturnsError()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Backend\Model\UrlInterface'
        )->turnOffSecretKey();

        /** @var \Magento\Framework\Data\Form\FormKey $formKey */
        $formKey = $this->_objectManager->get('Magento\Framework\Data\Form\FormKey');
        $post = [
            'login' => [
                'username' => \Magento\TestFramework\Bootstrap::ADMIN_NAME,
                'password' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD,
            ],
            'captcha' => ['backend_login' => 'some_unrealistic_captcha_value'],
            'form_key' => $formKey->getFormKey(),
        ];
        $this->getRequest()->setPostValue($post);
        $this->dispatch('backend/admin');
        $this->assertSessionMessages($this->equalTo([(string)__('Incorrect CAPTCHA.')]), MessageInterface::TYPE_ERROR);
        $backendUrlModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Backend\Model\UrlInterface'
        );
        $backendUrlModel->turnOffSecretKey();
        $url = $backendUrlModel->getUrl('admin');
        $this->assertRedirect($this->stringStartsWith($url));
    }
}
