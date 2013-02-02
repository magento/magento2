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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
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
     * @var Magento_Test_ObjectManager
     */
    protected $_objectManager;

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
     */
    protected function setUp()
    {
        $this->_objectManager = Mage::getObjectManager();
        $this->_runOptions[Mage::INIT_OPTION_REQUEST]  = $this->getRequest();
        $this->_runOptions[Mage::INIT_OPTION_RESPONSE] = $this->getResponse();
    }

    protected function tearDown()
    {
        $this->_request = null;
        $this->_response = null;
        $this->_objectManager = null;
        $this->_runOptions = array();
    }

    /**
     * Run request
     *
     * @param string $uri
     */
    public function dispatch($uri)
    {
        $this->getRequest()->setRequestUri($uri);
        $this->_getBootstrap()->runApp($this->_runOptions);
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
            $this->_objectManager->addSharedInstance($this->_request, 'Mage_Core_Controller_Request_Http');
        }
        return $this->_request;
    }

    /**
     * Response getter
     *
     * @return Magento_Test_Response
     */
    public function getResponse()
    {
        if (!$this->_response) {
            $this->_response = new Magento_Test_Response();
            $this->_objectManager->addSharedInstance($this->_response, 'Mage_Core_Controller_Response_Http');
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
     * Examples of usage:
     * $this->assertRedirect($this->equalTo($expectedUrl));
     * $this->assertRedirect($this->stringStartsWith($expectedUrlPrefix));
     * $this->assertRedirect($this->stringEndsWith($expectedUrlSuffix));
     * $this->assertRedirect($this->stringContains($expectedUrlSubstring));
     *
     * @param PHPUnit_Framework_Constraint|null $urlConstraint
     */
    public function assertRedirect(PHPUnit_Framework_Constraint $urlConstraint = null)
    {
        $this->assertTrue($this->getResponse()->isRedirect());
        if ($urlConstraint) {
            $actualUrl = '';
            foreach ($this->getResponse()->getHeaders() as $header) {
                if ($header['name'] == 'Location') {
                    $actualUrl = $header['value'];
                    break;
                }
            }
            $this->assertThat($actualUrl, $urlConstraint, 'Redirection URL does not match expectations');
        }
    }
}
