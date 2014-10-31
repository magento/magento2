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

namespace Magento\Framework\View\Layout;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class BuilderFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\View\Layout\BuilderFactory
     */
    protected $buildFactory;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->buildFactory = $this->objectManagerHelper->getObject(
            'Magento\Framework\View\Layout\BuilderFactory',
            [
                'objectManager' => $this->objectManagerMock,
                'typeMap' => [
                    [
                        'type' => 'invalid_type',
                        'class' => 'Magento\Framework\View\Layout\BuilderFactory'
                    ]
                ]
            ]
        );

    }

    /**
     * @param string $type
     * @param array $arguments
     *
     * @dataProvider createDataProvider
     */
    public function testCreate($type, $arguments, $layoutBuilderClass)
    {
        $layoutBuilderMock = $this->getMockBuilder('Magento\Framework\View\Layout\Builder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($layoutBuilderClass, $arguments)
            ->willReturn($layoutBuilderMock);

        $this->buildFactory->create($type, $arguments);
    }

    public function createDataProvider()
    {
        return [
            'layout_type' => [
                'type' => \Magento\Framework\View\Layout\BuilderFactory::TYPE_LAYOUT,
                'arguments' => ['key' => 'val'],
                'layoutBuilderClass' => 'Magento\Framework\View\Layout\Builder'
            ]
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateInvalidData()
    {
        $this->buildFactory->create('some_wrong_type', []);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateWithNonBuilderClass()
    {
        $wrongClass = $this->getMockBuilder('Magento\Framework\View\Layout\BuilderFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn($wrongClass);

        $this->buildFactory->create('invalid_type', []);
    }
}
