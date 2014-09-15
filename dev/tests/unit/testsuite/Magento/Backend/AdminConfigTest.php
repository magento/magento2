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

namespace Magento\Backend;

use Magento\TestFramework\ObjectManager;

/**
 * Test class for \Magento\Backend\AdminConfig
 *
 */
class AdminConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $requestMock;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    private $objectManager;


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
        $this->objectManager =  new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    public function testSetCookiePathNonDefault()
    {
        $mockFrontNameResolver = $this->getMockBuilder('\Magento\Backend\App\Area\FrontNameResolver')
            ->disableOriginalConstructor()
            ->getMock();

        $mockFrontNameResolver->expects($this->once())
            ->method('getFrontName')
            ->will($this->returnValue('backend'));

        $adminConfig = $this->objectManager->getObject(
            'Magento\Backend\AdminConfig',
            [
                'request' => $this->requestMock,
                'frontNameResolver' => $mockFrontNameResolver,
            ]
        );

        $this->assertEquals('/backend', $adminConfig->getCookiePath());
    }

    /**
     * Test for setting session name for admin
     *
     */
    public function testSetSessionNameByConstructor()
    {
        $sessionName = 'admin';
        $adminConfig = $this->objectManager->getObject(
            'Magento\Backend\AdminConfig',
            [
                'request' => $this->requestMock,
                'sessionName' => $sessionName
            ]
        );
        $this->assertSame($sessionName, $adminConfig->getName());
    }
}
