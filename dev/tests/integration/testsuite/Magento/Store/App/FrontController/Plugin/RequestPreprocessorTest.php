<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\App\FrontController\Plugin;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Form\FormKey;
use Magento\TestFramework\Response;
use Zend\Stdlib\Parameters;

/**
 * Class RequestPreprocessorTest @covers \Magento\Store\App\FrontController\Plugin\RequestPreprocessor.
 */
class RequestPreprocessorTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * Holder for base url.
     *
     * @var string
     */
    private $baseUrl;

    /**
     * Test non-secure POST request is redirected right away on completely secure frontend.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testHttpsRedirectNonSecureLoginPost()
    {
        $this->setFrontendCompletelySecure();
        $request = $this->prepareRequest();
        $app = $this->_objectManager->create(\Magento\Framework\App\Http::class, ['_request' => $request]);
        $response = $app->launch();
        $redirectUrl = str_replace('http://', 'https://', $this->baseUrl) .
            'index.php/customer/account/loginPost/';
        $this->assertResponseRedirect($response, $redirectUrl);
        $this->assertFalse($this->_objectManager->get(Session::class)->isLoggedIn());
        $this->setFrontendCompletelySecureRollback();
    }

    /**
     * Test secure POST request passed on completely secure frontend.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testHttpsPassSecureLoginPost()
    {
        $this->setFrontendCompletelySecure();
        $this->prepareRequest(true);
        $this->dispatch('customer/account/loginPost/');
        $redirectUrl = str_replace('http://', 'https://', $this->baseUrl) .
            'index.php/customer/account/';
        $this->assertResponseRedirect($this->getResponse(), $redirectUrl);
        $this->assertTrue($this->_objectManager->get(Session::class)->isLoggedIn());
        $this->setFrontendCompletelySecureRollback();
    }

    /**
     * Assert response is redirect with https protocol.
     *
     * @param Response $response
     * @param string $redirectUrl
     * @return void
     */
    private function assertResponseRedirect(Response $response, string $redirectUrl)
    {
        $this->assertTrue($response->isRedirect());
        $this->assertSame($redirectUrl, $response->getHeader('Location')->getUri());
    }

    /**
     * Prepare secure and non-secure requests for customer login.
     *
     * @param bool $isSecure
     * @return \Magento\TestFramework\Request
     */
    private function prepareRequest(bool $isSecure = false)
    {
        $post = new Parameters([
            'form_key' => $this->_objectManager->get(FormKey::class)->getFormKey(),
            'login' => [
                'username' => 'customer@example.com',
                'password' => 'password'
            ]
        ]);
        $request = $this->getRequest();
        $request->setMethod(\Magento\TestFramework\Request::METHOD_POST);
        $request->setRequestUri('customer/account/loginPost/');
        $request->setPost($post);
        if ($isSecure) {
            $server = new Parameters([
                'HTTPS' => 'on',
                'SERVER_PORT' => 443
            ]);
            $request->setServer($server);
        }

        return $request;
    }

    /**
     * Set use secure on frontend and set base url protocol to https.
     *
     * @return void
     */
    private function setFrontendCompletelySecure()
    {
        $configValue = $this->_objectManager->create(Value::class);
        $configValue->load('web/unsecure/base_url', 'path');
        $this->baseUrl = $configValue->getValue() ?: 'http://localhost/';
        $secureBaseUrl = str_replace('http://', 'https://', $this->baseUrl);
        if (!$configValue->getPath()) {
            $configValue->setPath('web/unsecure/base_url');
        }
        $configValue->setValue($secureBaseUrl);
        $configValue->save();
        $configValue = $this->_objectManager->create(Value::class);
        $configValue->load('web/secure/use_in_frontend', 'path');
        if (!$configValue->getPath()) {
            $configValue->setPath('web/secure/use_in_frontend');
        }
        $configValue->setValue(1);
        $configValue->save();
        $reinitibleConfig = $this->_objectManager->create(ReinitableConfigInterface::class);
        $reinitibleConfig->reinit();
    }

    /**
     * Unset use secure on frontend and set base url protocol to http.
     *
     * @return void
     */
    private function setFrontendCompletelySecureRollback()
    {
        $configValue = $this->_objectManager->create(Value::class);
        $unsecureBaseUrl = str_replace('https://', 'http://', $this->baseUrl);
        $configValue->load('web/unsecure/base_url', 'path');
        $configValue->setValue($unsecureBaseUrl);
        $configValue->save();
        $configValue = $this->_objectManager->create(Value::class);
        $configValue->load('web/secure/use_in_frontend', 'path');
        $configValue->setValue(0);
        $configValue->save();
        $reinitibleConfig = $this->_objectManager->create(ReinitableConfigInterface::class);
        $reinitibleConfig->reinit();
    }
}
