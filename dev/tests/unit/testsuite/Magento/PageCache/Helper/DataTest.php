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
 * Test class for \Magento\PageCache\Helper\Data
 */
namespace Magento\PageCache\Helper;

/**
 * Class DataTest
 *
 * @package Magento\PageCache\Controller
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\App\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\PageCache\Helper\Data
     */
    protected $helper;

    /**
     * Set up before test
     */
    protected function setUp()
    {
        $context = $this->getMockBuilder('\Magento\App\Helper\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this->getMockBuilder('\Magento\App\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = new \Magento\PageCache\Helper\Data($context, $this->configMock);
    }

    public function testGetPublicMaxAgeCache()
    {
        $age = 0;
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with($this->equalTo(\Magento\PageCache\Helper\Data::PUBLIC_MAX_AGE_PATH))
            ->will($this->returnValue($age));
        $data = $this->helper->getPublicMaxAgeCache();
        $this->assertEquals($age, $data);
    }

    public function testMaxAgeCache()
    {
        // one year
        $age = 365 * 24 * 60 * 60;
        $this->assertEquals($age, \Magento\PageCache\Helper\Data::PRIVATE_MAX_AGE_CACHE);
    }
}
