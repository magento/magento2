<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Observer;

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

        $post = [
            'login' => [
                'username' => \Magento\TestFramework\Bootstrap::ADMIN_NAME,
                'password' => \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD,
            ],
            'captcha' => ['backend_login' => 'some_unrealistic_captcha_value'],
        ];
        $this->getRequest()->setPostValue($post);
        $this->dispatch('backend/admin');
        $this->assertContains((string)__('Incorrect CAPTCHA'), $this->getResponse()->getBody());
    }
}
