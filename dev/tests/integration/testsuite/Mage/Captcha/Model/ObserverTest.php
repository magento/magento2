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
 * @category    Magento
 * @package     Magento_Captcha
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test captcha observer behavior
 */
class Mage_Captcha_Model_ObserverTest extends Magento_Test_TestCase_ControllerAbstract
{
    /**
     * Empty fixture to wrap tests in db transaction
     */
    public static function emptyFixture()
    {

    }

    /**
     * @magentoConfigFixture admin_store admin/captcha/forms backend_login
     * @magentoConfigFixture admin_store admin/captcha/enable 1
     * @magentoConfigFixture admin_store admin/captcha/mode always
     */
    public function testBackendLoginActionWithInvalidCaptchaReturnsError()
    {
        Mage::getSingleton('Mage_Backend_Model_Url')->turnOffSecretKey();

        $post = array(
            'login' => array(
                'username' => Magento_Test_Bootstrap::ADMIN_NAME,
                'password' => Magento_Test_Bootstrap::ADMIN_PASSWORD
            ),
            'captcha' => array(
                'backend_login' => 'some_unrealistic_captcha_value',
            )
        );
        $this->getRequest()->setPost($post);
        $this->dispatch('/admin');
        $this->assertContains(Mage::helper('Mage_Captcha')->__('Incorrect CAPTCHA.'), $this->getResponse()->getBody());
    }

    /**
     * @magentoConfigFixture current_store admin/captcha/enable 1
     * @magentoConfigFixture current_store admin/captcha/forms backend_login
     * @magentoConfigFixture current_store admin/captcha/mode after_fail
     * @magentoConfigFixture current_store admin/captcha/failed_attempts_login 1
     * @magentoDataFixture emptyFixture
     * @magentoAppIsolation enabled
     */
    public function testCaptchaIsRequiredAfterFailedLoginAttempts()
    {
        Mage::app()->setCurrentStore(0);
        $captchaModel = Mage::helper('Mage_Captcha_Helper_Data')->getCaptcha('backend_login');

        try {
            $authModel = new Mage_Backend_Model_Auth();
            $authModel->login(
                Magento_Test_Bootstrap::ADMIN_NAME,
                'wrong_password'
            );
        }
        catch (Exception $e) {
        }

        $this->assertTrue($captchaModel->isRequired());
    }
}
