<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Setup\Controller;

class WebConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * To prevent save value of $SERVER, which is modified in this test
     *
     * @var array
     */
    private $serverArray;

    public function setUp()
    {
        $this->serverArray = $_SERVER;
    }

    public function tearDown()
    {
        $_SERVER = $this->serverArray;
    }

    public function testIndexAction()
    {
        /** @var $controller WebConfiguration */
        $controller = new WebConfiguration();
        $_SERVER['DOCUMENT_ROOT'] = 'some/doc/root/value';
        $viewModel = $controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
        $this->assertArrayHasKey('autoBaseUrl', $viewModel->getVariables());
    }
}
