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

class DesignTest extends \PHPUnit_Framework_TestCase
{
    public function testAroundDispatch()
    {
        $subjectMock = $this->getMock('Magento\Framework\App\Action\Action', array(), array(), '', false);
        $designLoaderMock = $this->getMock('Magento\Framework\View\DesignLoader', array(), array(), '', false);
        $closureMock = function () {
            return 'Expected';
        };
        $requestMock = $this->getMock('Magento\Framework\App\RequestInterface');
        $plugin = new \Magento\Core\App\Action\Plugin\Design($designLoaderMock);
        $designLoaderMock->expects($this->once())->method('load');
        $this->assertEquals('Expected', $plugin->aroundDispatch($subjectMock, $closureMock, $requestMock));
    }
}
