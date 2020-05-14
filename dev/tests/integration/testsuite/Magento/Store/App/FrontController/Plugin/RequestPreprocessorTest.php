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
use Laminas\Stdlib\Parameters;

/**
 * Tests \Magento\Store\App\FrontController\Plugin\RequestPreprocessor.
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
     * @var array;
     */
    private $config;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->config = [];
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $this->setConfig($this->config);
        parent::tearDown();
    }

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
            'customer/account/';
        $this->assertResponseRedirect($this->getResponse(), $redirectUrl);
        $this->assertTrue($this->_objectManager->get(Session::class)->isLoggedIn());
        $this->setFrontendCompletelySecureRollback();
    }

    /**
     * Test auto redirect to base URL
     *
     * @param array $config
     * @param string $requestUrl
     * @param string $redirectUrl
     * @magentoAppArea frontend
     * @dataProvider autoRedirectToBaseURLDataProvider
     */
    public function testAutoRedirectToBaseURL(array $config, string $requestUrl, string $redirectUrl)
    {
        $request = [
            'REQUEST_SCHEME' => parse_url($requestUrl, PHP_URL_SCHEME),
            'SERVER_NAME' => parse_url($requestUrl, PHP_URL_HOST),
            'SCRIPT_NAME' => '/index.php',
            'SCRIPT_FILENAME' => 'index.php',
            'REQUEST_URI' => parse_url($requestUrl, PHP_URL_PATH),
        ];
        $this->setConfig($config);
        $this->setServer($request);
        $app = $this->_objectManager->create(\Magento\Framework\App\Http::class, ['_request' => $this->getRequest()]);
        $this->_response = $app->launch();
        $this->assertRedirect($this->equalTo($redirectUrl));
    }

    /**
     * @return array
     */
    public function autoRedirectToBaseURLDataProvider(): array
    {
        $baseConfig = [
            'web/unsecure/base_url' => 'http://magento.com/us/',
            'web/session/use_frontend_sid' => 0,
            'web/seo/use_rewrites' => 1,
        ];

        return [
            [
                'config' => $baseConfig,
                'request' => 'http://magento.com/a/b/c/d/e.html',
                'redirectUrl' => 'http://magento.com/us/a/b/c/d/e.html'
            ],
            [
                'config' => $baseConfig,
                'request' => 'http://magento.com/a/b/c/d.html',
                'redirectUrl' => 'http://magento.com/us/a/b/c/d.html'
            ],
            [
                'config' => $baseConfig,
                'request' => 'http://magento.com/a/b/c.html',
                'redirectUrl' => 'http://magento.com/us/a/b/c.html'
            ],
            [
                'config' => $baseConfig,
                'request' => 'http://magento.com/a/b.html',
                'redirectUrl' => 'http://magento.com/us/a/b.html'
            ],
            [
                'config' => $baseConfig,
                'request' => 'http://magento.com/a.html',
                'redirectUrl' => 'http://magento.com/us/a.html'
            ],
            [
                'config' => $baseConfig,
                'request' => 'http://magento.com/a/b/c/d/e',
                'redirectUrl' => 'http://magento.com/us/a/b/c/d/e'
            ],
            [
                'config' => $baseConfig,
                'request' => 'http://magento.com/a/b/c/d',
                'redirectUrl' => 'http://magento.com/us/a/b/c/d'
            ],
            [
                'config' => $baseConfig,
                'request' => 'http://magento.com/a/b/c',
                'redirectUrl' => 'http://magento.com/us/a/b/c'
            ],
            [
                'config' => $baseConfig,
                'request' => 'http://magento.com/a/b',
                'redirectUrl' => 'http://magento.com/us/a/b'
            ],
            [
                'config' => $baseConfig,
                'request' => 'http://magento.com/a',
                'redirectUrl' => 'http://magento.com/us/a'
            ],
            [
                'config' => $baseConfig,
                'request' => 'http://magento.com/',
                'redirectUrl' => 'http://magento.com/us/'
            ],
            [
                'config' => array_merge($baseConfig, ['web/seo/use_rewrites' => 0]),
                'request' => 'http://magento.com/',
                'redirectUrl' => 'http://magento.com/us/index.php/'
            ],
            [
                'config' => array_merge($baseConfig, ['web/seo/use_rewrites' => 0]),
                'request' => 'http://magento.com/a/b/c/d.html',
                'redirectUrl' => 'http://magento.com/us/index.php/a/b/c/d.html'
            ],
            [
                'config' => array_merge($baseConfig, ['web/seo/use_rewrites' => 0]),
                'request' => 'http://magento.com/a/b/c/d',
                'redirectUrl' => 'http://magento.com/us/index.php/a/b/c/d'
            ],
        ];
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
        $post = new Parameters(
            [
                'form_key' => $this->_objectManager->get(FormKey::class)->getFormKey(),
                'login' => [
                    'username' => 'customer@example.com',
                    'password' => 'password'
                ]
            ]
        );
        $request = $this->getRequest();
        $request->setMethod(\Magento\TestFramework\Request::METHOD_POST);
        $request->setRequestUri('customer/account/loginPost/');
        $request->setPost($post);
        if ($isSecure) {
            $server = new Parameters(
                [
                    'HTTPS' => 'on',
                    'SERVER_PORT' => 443
                ]
            );
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

    private function setConfig(array $config): void
    {
        foreach ($config as $path => $value) {
            $model = $this->_objectManager->create(Value::class);
            $model->load($path, 'path');
            if (!isset($this->config[$path])) {
                $this->config[$path] = $model->getValue();
            }
            if (!$model->getPath()) {
                $model->setPath($path);
            }
            if ($value !== null) {
                $model->setValue($value);
                $model->save();
            } elseif ($model->getId()) {
                $model->delete();
            }
        }
        $this->_objectManager->create(ReinitableConfigInterface::class)->reinit();
    }

    private function setServer(array $server)
    {
        $request = $this->getRequest();
        $properties = [
            'baseUrl',
            'basePath',
            'requestUri',
            'originalPathInfo',
            'pathInfo',
        ];
        $reflection = new \ReflectionClass($request);

        foreach ($properties as $name) {
            $property = $reflection->getProperty($name);
            $property->setAccessible(true);
            $property->setValue($request, null);
        }
        $request->setServer(new Parameters($server));
    }
}
