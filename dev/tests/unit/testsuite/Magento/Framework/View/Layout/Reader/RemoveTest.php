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
 * Test class for \Magento\Framework\View\Layout\Reader\Remove
 */
namespace Magento\Framework\View\Layout\Reader;

class RemoveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Layout\Reader\Remove
     */
    protected $model;

    /**
     * @var \Magento\Framework\View\Layout\Reader\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\View\Layout\Element
     */
    protected $element;

    /**
     * @var \Magento\Framework\View\Layout\ScheduledStructure|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scheduledStructure;

    public function setUp()
    {
        $this->context = $this->getMockBuilder('Magento\Framework\View\Layout\Reader\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->scheduledStructure = $this->getMockBuilder('Magento\Framework\View\Layout\ScheduledStructure')
            ->disableOriginalConstructor()->setMethods(['setElementToRemoveList', '__wakeup'])
            ->getMock();
        $this->model = new Remove;
    }

    public function testGetSupportedNodes()
    {
        $data[] = \Magento\Framework\View\Layout\Reader\Remove::TYPE_REMOVE;
        $this->assertEquals($data, $this->model->getSupportedNodes());
    }

    /**
     * @dataProvider processDataProvider
     */
    public function testProcess($xml)
    {
        $this->element = new \Magento\Framework\View\Layout\Element($xml);
        $this->context->expects($this->any())
            ->method('getScheduledStructure')
            ->will($this->returnValue($this->scheduledStructure));
        $this->scheduledStructure->expects($this->once())->method('setElementToRemoveList')->with('header');
        $this->model->interpret($this->context, $this->element, $this->element);
    }

    public function processDataProvider()
    {
        return [
            [
                '<remove name="header"/>'
            ]
        ];
    }
}
