<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Result;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class PageFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\View\Result\PageFactory */
    protected $pageFactory;

    /** @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject */
    protected $page;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $objectManagerMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->pageFactory = $this->objectManagerHelper->getObject(
            'Magento\Framework\View\Result\PageFactory',
            [
                'objectManager' => $this->objectManagerMock
            ]
        );
        $this->page = $this->getMockBuilder('Magento\Framework\View\Result\Page')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testCreate()
    {
        $this->objectManagerMock->expects($this->once())->method('create')->with('Magento\Framework\View\Result\Page')
            ->will($this->returnValue($this->page));
        $this->assertSame($this->page, $this->pageFactory->create());
    }
}
