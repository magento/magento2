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

namespace Magento\Framework\View\Layout\Reader;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class PoolTest extends \PHPUnit_Framework_TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\View\Layout\Reader\Pool */
    protected $pool;

    /** @var \Magento\Framework\View\Layout\ReaderFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $readerFactoryMock;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->readerFactoryMock = $this->getMockBuilder('Magento\Framework\View\Layout\ReaderFactory')
            ->disableOriginalConstructor()->getMock();

        $this->pool = $this->objectManagerHelper->getObject(
            'Magento\Framework\View\Layout\Reader\Pool',
            [
                'readerFactory' => $this->readerFactoryMock,
                'readers' => [
                    'move' => 'Magento\Framework\View\Layout\Reader\Move',
                    'remove' => 'Magento\Framework\View\Layout\Reader\Remove',
                ]
            ]
        );
    }

    public function testReadStructure()
    {
        /** @var Context $contextMock */
        $contextMock = $this->getMockBuilder('Magento\Framework\View\Layout\Reader\Context')
            ->disableOriginalConstructor()->getMock();

        $currentElement = new \Magento\Framework\View\Layout\Element(
            '<element><move name="block"/><remove name="container"/><ignored name="user"/></element>'
        );

        /**
         * @var \Magento\Framework\View\Layout\Reader\Move|\PHPUnit_Framework_MockObject_MockObject $moveReaderMock
         */
        $moveReaderMock = $this->getMockBuilder('Magento\Framework\View\Layout\Reader\Move')
            ->disableOriginalConstructor()->getMock();
        $moveReaderMock->expects($this->once())->method('process')
            ->willReturn($this->returnSelf());
        $moveReaderMock->method('getSupportedNodes')
            ->willReturn(['move']);

        /**
         * @var \Magento\Framework\View\Layout\Reader\Remove|\PHPUnit_Framework_MockObject_MockObject $removeReaderMock
         */
        $removeReaderMock = $this->getMockBuilder('Magento\Framework\View\Layout\Reader\Remove')
            ->disableOriginalConstructor()->getMock();
        $removeReaderMock->expects($this->once())->method('process')
            ->with()
            ->willReturn($this->returnSelf());
        $removeReaderMock->method('getSupportedNodes')
            ->willReturn(['remove']);

        $this->readerFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValueMap(
                [
                    ['Magento\Framework\View\Layout\Reader\Move', [], $moveReaderMock],
                    ['Magento\Framework\View\Layout\Reader\Remove', [], $removeReaderMock]
                ]
            ));

        $this->pool->readStructure($contextMock, $currentElement);
    }
}
