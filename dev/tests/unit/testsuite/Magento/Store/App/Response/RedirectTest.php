<?php
/**
 * Response redirector tests
 *
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Store\App\Response;

class RedirectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\App\Response\Redirect
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlCoderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sidResolverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_urlBuilderMock;

    protected function setUp()
    {
        $this->_requestMock = $this->getMock(
            'Magento\Framework\App\RequestInterface',
            array(
                'getServer',
                'getModuleName',
                'setModuleName',
                'getActionName',
                'setActionName',
                'getParam',
                'getCookie'
            )
        );
        $this->_storeManagerMock = $this->getMock('\Magento\Framework\StoreManagerInterface');
        $this->_urlCoderMock = $this->getMock(
            '\Magento\Framework\Encryption\UrlCoder',
            array(),
            array(),
            '',
            false
        );
        $this->_sessionMock = $this->getMock('\Magento\Framework\Session\SessionManagerInterface');
        $this->_sidResolverMock = $this->getMock('\Magento\Framework\Session\SidResolverInterface');
        $this->_urlBuilderMock = $this->getMock('\Magento\Framework\UrlInterface');

        $this->_model = new \Magento\Store\App\Response\Redirect(
            $this->_requestMock,
            $this->_storeManagerMock,
            $this->_urlCoderMock,
            $this->_sessionMock,
            $this->_sidResolverMock,
            $this->_urlBuilderMock
        );
    }

    /**
     * @dataProvider urlAddresses
     * @param string $baseUrl
     * @param string $successUrl
     */
    public function testSuccessUrl($baseUrl, $successUrl)
    {
        $testStoreMock = $this->getMock('\Magento\Store\Model\Store', array(), array(), '', false);
        $testStoreMock->expects($this->any())->method('getBaseUrl')->will($this->returnValue($baseUrl));
        $this->_requestMock->expects($this->any())->method('getParam')->will($this->returnValue(null));
        $this->_storeManagerMock->expects($this->any())->method('getStore')
            ->will($this->returnValue($testStoreMock));
        $this->assertEquals($baseUrl, $this->_model->success($successUrl));
    }

    /**
     * DataProvider with the test urls
     *
     * @return array
     */
    public function urlAddresses()
    {
        return array(
            array(
                'http://externalurl.com/',
                'http://internalurl.com/'
            ),
            array(
                'http://internalurl.com/',
                'http://internalurl.com/'
            )
        );
    }
}
