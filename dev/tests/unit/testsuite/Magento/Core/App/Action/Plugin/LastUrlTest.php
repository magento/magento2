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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\App\Action\Plugin;

class LastUrlTest extends \PHPUnit_Framework_TestCase
{
    public function testAfterDispatch()
    {
        $session = $this->getMock('\Magento\Framework\Session\Generic', array('setLastUrl'), array(), '', false);
        $subjectMock = $this->getMock('Magento\Framework\App\Action\Action', array(), array(), '', false);
        $closureMock = function () {
            return 'result';
        };
        $requestMock = $this->getMock('Magento\Framework\App\RequestInterface');
        $url = $this->getMock('\Magento\Framework\Url', array(), array(), '', false);
        $plugin = new \Magento\Core\App\Action\Plugin\LastUrl($session, $url);
        $session->expects($this->once())->method('setLastUrl')->with('http://example.com');
        $url->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            '*/*/*',
            array('_current' => true)
        )->will(
            $this->returnValue('http://example.com')
        );
        $this->assertEquals('result', $plugin->aroundDispatch($subjectMock, $closureMock, $requestMock));
    }
}
