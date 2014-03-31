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
 * @package     Magento_PageCache
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\PageCache\Controller\Adminhtml/PageCache
 */
namespace Magento\PageCache\Controller\Adminhtml;

/**
 * Class PageCacheTest
 *
 * @package Magento\PageCache\Controller\Adminhtml
 */
class PageCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\App\View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\PageCache\Controller\Adminhtml\PageCache
     */
    protected $controller;

    /**
     * @var \Magento\App\Response\Http\FileFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileFactoryMock;

    /**
     * @var \Magento\PageCache\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * Set up before test
     */
    protected function setUp()
    {
        $this->fileFactoryMock = $this->getMockBuilder(
            'Magento\App\Response\Http\FileFactory'
        )->disableOriginalConstructor()->getMock();
        $this->configMock = $this->getMockBuilder(
            'Magento\PageCache\Model\Config'
        )->disableOriginalConstructor()->getMock();
        $contextMock = $this->getMockBuilder(
            'Magento\Backend\App\Action\Context'
        )->disableOriginalConstructor()->getMock();

        $this->requestMock = $this->getMockBuilder(
            'Magento\App\Request\Http'
        )->disableOriginalConstructor()->getMock();
        $this->responseMock = $this->getMockBuilder(
            'Magento\App\Response\Http'
        )->disableOriginalConstructor()->getMock();
        $this->viewMock = $this->getMockBuilder('Magento\App\View')->disableOriginalConstructor()->getMock();

        $contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())->method('getResponse')->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())->method('getView')->will($this->returnValue($this->viewMock));

        $this->controller = new \Magento\PageCache\Controller\Adminhtml\PageCache(
            $contextMock,
            $this->fileFactoryMock,
            $this->configMock
        );
    }

    public function testExportVarnishConfigAction()
    {
        $fileContent = 'some conetnt';
        $filename = 'varnish.vcl';
        $responseMock = $this->getMockBuilder(
            'Magento\App\ResponseInterface'
        )->disableOriginalConstructor()->getMock();

        $this->configMock->expects($this->once())->method('getVclFile')->will($this->returnValue($fileContent));
        $this->fileFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo($filename),
            $this->equalTo($fileContent),
            $this->equalTo(\Magento\App\Filesystem::VAR_DIR)
        )->will(
            $this->returnValue($responseMock)
        );

        $result = $this->controller->exportVarnishConfigAction();
        $this->assertInstanceOf('Magento\App\ResponseInterface', $result);
    }
}
