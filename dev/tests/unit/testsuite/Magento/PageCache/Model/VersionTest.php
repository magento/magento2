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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\PageCache\Model;

/**
 * Class VersionTest
 * @package Magento\PageCache\Model
 */
class VersionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Cookie mock
     *
     * @var \Magento\Stdlib\Cookie|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cookieMock;

    /**
     * Request mock
     *
     * @var \Magento\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * Version instance
     *
     * @var Version
     */
    private $versionInstance;

    /**
     * Create cookie and request mock, version instance
     */
    public function setUp()
    {
        $this->cookieMock = $this->getMock('Magento\Stdlib\Cookie', array('set'), array(), '', false);
        $this->requestMock = $this->getMock('Magento\App\Request\Http', array('isPost'), array(), '', false);
        $this->versionInstance =  new Version($this->cookieMock, $this->requestMock);
    }

    /**
     * Handle private content version cookie
     * Set cookie if it is not set.
     * Increment version on post requests.
     * In all other cases do nothing.
     */
    /**
     * @dataProvider processProvider
     * @param bool $isPost
     */
    public function testProcess($isPost)
    {
        $this->requestMock->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue($isPost));
        if ($isPost) {
            $this->cookieMock->expects($this->once())->method('set');
        }
        $this->versionInstance->process();
    }

    /**
     * Data provider for testProcess
     * @return array
     */
    public function processProvider()
    {
        return array(
            array(true),
            array(false)
        );
    }
}
