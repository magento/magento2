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
 * Test class for \Magento\PageCache\Helper\Data
 */
namespace Magento\PageCache\Helper;

/**
 * Class DataTest
 *
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\View\Layout\ProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $updateLayoutMock;

    /**
     * @var \Magento\Framework\App\Helper\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\App\View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    public function testMaxAgeCache()
    {
        // one year
        $age = 365 * 24 * 60 * 60;
        $this->assertEquals($age, \Magento\PageCache\Helper\Data::PRIVATE_MAX_AGE_CACHE);
    }

    /**
     * test for getActualHandles function
     */
    public function testGetActualHandles()
    {
        $this->prepareMocks();
        $layoutHandles = [
            'handle1',
            'config_layout_handle1',
            'handle2'
        ];

        $this->updateLayoutMock->expects($this->once())
            ->method('getHandles')
            ->will($this->returnValue($layoutHandles));

        $this->assertEquals($layoutHandles, $this->helper->getActualHandles());
    }

    protected function prepareMocks()
    {
        $this->contextMock = $this->getMock('Magento\Framework\App\Helper\Context', [], [], '', false);
        $this->viewMock =
            $this->getMock('Magento\Framework\App\View', ['getLayout'], ['getPageLayoutHandles'], '', false);
        $layoutMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\LayoutInterface',
            array(),
            '',
            false,
            true,
            true,
            array('getUpdate')
        );
        $this->updateLayoutMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Layout\ProcessorInterface',
            array(),
            '',
            false,
            true,
            true,
            array()
        );

        $this->viewMock->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($layoutMock));
        $layoutMock->expects($this->once())
            ->method('getUpdate')
            ->will($this->returnValue($this->updateLayoutMock));

        $this->helper = new Data($this->contextMock, $this->viewMock);
    }
}
