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

/**
 * Test class for \Magento\Backend\AdminConfig
 */
namespace Magento\Backend;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

class AdminConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $requestMock;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;


    protected function setUp()
    {
        $this->requestMock = $this->getMock(
            '\Magento\Framework\App\Request\Http',
            ['getBasePath', 'isSecure', 'getHttpHost'],
            [],
            '',
            false,
            false
        );
        $this->requestMock->expects($this->atLeastOnce())->method('getBasePath')->will($this->returnValue('/'));
        $this->requestMock->expects(
            $this->atLeastOnce()
        )->method(
            'getHttpHost'
        )->will(
            $this->returnValue('init.host')
        );
        $this->appState = $this->getMock('\Magento\Framework\App\State',
            ['isInstalled'], [], '', false, false);
        $this->appState->expects($this->atLeastOnce())->method('isInstalled')->will($this->returnValue(true));
    }

    public function testSetCookiePathNonDefault()
    {
        $mockFrontNameResolver = $this->getMockBuilder('\Magento\Backend\App\Area\FrontNameResolver')
            ->disableOriginalConstructor()
            ->getMock();

        $mockFrontNameResolver
            ->method('getFrontName')
            ->will($this->returnValue('backend'));

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $adminConfig = $objectManager->getObject(
            'Magento\Backend\AdminConfig',
            [
                'request' => $this->requestMock,
                'appState' => $this->appState,
                'frontNameResolver' => $mockFrontNameResolver,
            ]
        );

        $this->assertEquals('/backend', $adminConfig->getCookiePath());
    }
}
