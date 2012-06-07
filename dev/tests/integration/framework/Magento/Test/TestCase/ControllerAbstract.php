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
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract class for the controller tests
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.numberOfChildren)
 */
abstract class Magento_Test_TestCase_ControllerAbstract extends PHPUnit_Framework_TestCase
{

    const MODE_EQUALS       = 1;
    const MODE_START_WITH   = 2;
    const MODE_END_WITH     = 3;
    const MODE_CONTAINS     = 4;


    protected $_runCode     = '';
    protected $_runScope    = 'store';
    protected $_runOptions  = array();

    /**
     * @var Magento_Test_Request
     */
    protected $_request;

    /**
     * @var Magento_Test_Response
     */
    protected $_response;

    /**
     * Bootstrap instance getter
     *
     * @return Magento_Test_Bootstrap
     */
    protected function _getBootstrap()
    {
        return Magento_Test_Bootstrap::getInstance();
    }

    /**
     * Bootstrap application before eny test
     *
     * @return void
     */
    protected function setUp()
    {
        /**
         * Use run options from bootstrap
         */
        $this->_runOptions = $this->_getBootstrap()->getAppOptions();
        $this->_runOptions['request']   = $this->getRequest();
        $this->_runOptions['response']  = $this->getResponse();
    }

    /**
     * Run request
     *
     * @return void
     */
    public function dispatch($uri)
    {
        $this->getRequest()->setRequestUri($uri);
        Mage::run($this->_runCode, $this->_runScope, $this->_runOptions);
    }

    /**
     * Request getter
     *
     * @return Magento_Test_Request
     */
    public function getRequest()
    {
        if (!$this->_request) {
            $this->_request = new Magento_Test_Request();
        }
        return $this->_request;
    }

    /**
     * Response getter
     *
     * @return Zend_Controller_Response_Http
     */
    public function getResponse()
    {
        if (!$this->_response) {
            $this->_response = new Magento_Test_Response();
        }
        return $this->_response;
    }

    /**
     * Assert that response is '404 Not Found'
     */
    public function assert404NotFound()
    {
        $this->assertEquals('noRoute', $this->getRequest()->getActionName());
        $this->assertContains('404 Not Found', $this->getResponse()->getBody());
    }

    /**
     * Analyze response object and look for header with specified name, and assert a regex towards its value
     *
     * @param string $headerName
     * @param string $valueRegex
     * @throws PHPUnit_Framework_AssertionFailedError when header not found
     */
    public function assertHeaderPcre($headerName, $valueRegex)
    {
        $headerFound = false;
        $headers = $this->getResponse()->getHeaders();
        foreach ($headers as $header) {
            if ($header['name'] === $headerName) {
                $headerFound = true;
                $this->assertRegExp($valueRegex, $header['value']);
            }
        }
        if (!$headerFound) {
            $this->fail("Header '{$headerName}' was not found. Headers dump:\n" . var_export($headers, 1));
        }
    }

    /**
     * Assert that there is a redirect to expected URL.
     * Omit expected URL to check that redirect to wherever has been occurred.
     *
     * @param string|null $expectedUrl
     * @param int $checkMode - const
     */
    public function assertRedirect($expectedUrl = null, $checkMode = self::MODE_EQUALS)
    {
        $this->assertTrue($this->getResponse()->isRedirect());

        if ($expectedUrl) {
            switch ($checkMode) {
                case self::MODE_START_WITH: {
                    $this->_assertUrlStartsWith($expectedUrl);
                } break;

                case self::MODE_END_WITH: {
                    $this->_assertUrlEndsWith($expectedUrl);
                } break;

                case self::MODE_CONTAINS: {
                    $this->_assertUrlContains($expectedUrl);
                } break;

                default: {
                    $this->_assertUrlEquals($expectedUrl);
                } break;
            }
        }
    }

    /**
     * Assert that there is a redirect to URL that start with expected part
     *
     * @param string $expectedUrl
     */
    protected function _assertUrlStartsWith($expectedUrl)
    {
        $isRedirected = false;
        foreach ($this->getResponse()->getHeaders() as $header) {
            if ($header['name'] == 'Location') {
                $this->assertStringStartsWith($expectedUrl, $header['value'], 'Incorrect redirection URL');
                $isRedirected = true;
                break;
            }
        }
        $this->assertTrue($isRedirected, 'There is no redirection to expected page');
    }

    /**
     * Assert that there is a redirect to URL that ends with expected part
     *
     * @param string $expectedUrl
     */
    protected function _assertUrlEndsWith($expectedUrl)
    {
        $isRedirected = false;
        foreach ($this->getResponse()->getHeaders() as $header) {
            if ($header['name'] == 'Location') {
                $this->assertStringEndsWith($expectedUrl, $header['value'], 'Incorrect redirection URL');
                $isRedirected = true;
                break;
            }
        }
        $this->assertTrue($isRedirected, 'There is no redirection to expected page');
    }

    /**
     * Assert that there is a redirect to expected URL
     *
     * @param string $expectedUrl
     */
    protected function _assertUrlEquals($expectedUrl)
    {
        $expected = array(
            'name'    => 'Location',
            'value'   => $expectedUrl,
            'replace' => true,
        );
        $this->assertContains($expected, $this->getResponse()->getHeaders());
    }

    /**
     * Assert that there is a redirect to URL that contains expected part
     *
     * @param string $expectedUrl
     */
    protected function _assertUrlContains($expectedUrl)
    {
        $isRedirected = false;
        foreach ($this->getResponse()->getHeaders() as $header) {
            if ($header['name'] == 'Location') {
                $this->assertContains($expectedUrl, $header['value'], 'Incorrect page url');
                $isRedirected = true;
                break;
            }
        }
        $this->assertTrue($isRedirected, 'There is no redirection to expected page');
    }
}
