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
namespace Magento\Framework\View\Element;

class RendererListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Element\RendererList
     */
    protected $renderList;

    /**
     * @var \Magento\Framework\View\Element\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Framework\View\Element\AbstractBlock|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $blockMock;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->blockMock = $this->getMockBuilder('Magento\Framework\View\Element\AbstractBlock')
            ->setMethods(['setRenderedBlock', 'getTemplate', 'setTemplate'])->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->layoutMock = $this->getMockBuilder('Magento\Framework\View\LayoutInterface')
            ->setMethods(['getBlock', 'getChildName'])->disableOriginalConstructor()->getMockForAbstractClass();

        $this->layoutMock->expects($this->any())
            ->method('getBlock')
            ->will($this->returnValue($this->blockMock));

        $this->contextMock = $this->getMockBuilder('Magento\Framework\View\Element\Context')
            ->setMethods(['getLayout'])->disableOriginalConstructor()->getMock();

        $this->contextMock->expects($this->any())
            ->method('getLayout')
            ->will($this->returnValue($this->layoutMock));

        $this->renderList = $objectManagerHelper->getObject(
            'Magento\Framework\View\Element\RendererList',
            ['context' => $this->contextMock]
        );
    }

    public function testGetRenderer()
    {
        $this->blockMock->expects($this->any())
            ->method('setRenderedBlock')
            ->will($this->returnValue($this->blockMock));

        $this->blockMock->expects($this->any())
            ->method('getTemplate')
            ->will($this->returnValue('template'));

        $this->blockMock->expects($this->any())
            ->method('setTemplate')
            ->will($this->returnValue($this->blockMock));

        $this->layoutMock->expects($this->any())
            ->method('getChildName')
            ->will($this->returnValue(true));

        /** During the first call cache will be generated */
        $this->assertInstanceOf(
            '\Magento\Framework\View\Element\BlockInterface',
            $this->renderList->getRenderer('type', null, null)
        );
        /** Cached value should be returned during second call */
        $this->assertInstanceOf(
            '\Magento\Framework\View\Element\BlockInterface',
            $this->renderList->getRenderer('type', null, 'renderer_template')
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetRendererWithException()
    {
        $this->assertInstanceOf(
            '\Magento\Framework\View\Element\BlockInterface',
            $this->renderList->getRenderer(null)
        );
    }
}
