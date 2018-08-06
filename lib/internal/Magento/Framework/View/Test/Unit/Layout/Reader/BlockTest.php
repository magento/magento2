<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Framework\View\Layout\Reader\Block
 */
namespace Magento\Framework\View\Test\Unit\Layout\Reader;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout\AclCondition;
use Magento\Framework\View\Layout\ConfigCondition;
use Magento\Framework\View\Layout\Reader\Block;
use Magento\Framework\View\Layout\Reader\Visibility\Condition;

class BlockTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Layout\ScheduledStructure|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scheduledStructure;

    /**
     * @var \Magento\Framework\View\Layout\Reader\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\Framework\View\Layout\ReaderPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerPool;

    /**
     * @var \Magento\Framework\View\Layout\Element
     */
    protected $currentElement;

    /**
     * @param string $xml
     * @param string $elementType
     * @return \Magento\Framework\View\Layout\Element
     */
    protected function getElement($xml, $elementType)
    {
        $xml = '<' . Block::TYPE_BLOCK . '>'
            . $xml
            . '</' . Block::TYPE_BLOCK . '>';

        $xml = simplexml_load_string($xml, \Magento\Framework\View\Layout\Element::class);
        return $xml->{$elementType};
    }

    /**
     * Prepare reader pool
     *
     * @param string $xml
     * @param string $elementType
     */
    protected function prepareReaderPool($xml, $elementType)
    {
        $this->currentElement = $this->getElement($xml, $elementType);
        $this->readerPool->expects($this->once())->method('interpret')->with($this->context, $this->currentElement);
    }

    /**
     * Return testing instance of block
     *
     * @param array $arguments
     * @return Block
     */
    protected function getBlock(array $arguments)
    {
        return (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(\Magento\Framework\View\Layout\Reader\Block::class, $arguments);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->scheduledStructure = $this->createMock(\Magento\Framework\View\Layout\ScheduledStructure::class);
        $this->context = $this->createMock(\Magento\Framework\View\Layout\Reader\Context::class);
        $this->readerPool = $this->createMock(\Magento\Framework\View\Layout\ReaderPool::class);
    }

    /**
     * @param string $literal
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $scheduleStructureCount
     * @param string $ifconfigValue
     * @param array $expectedConditions
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $getCondition
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $setCondition
     * @param string $aclKey
     * @param string $aclValue
     *
     * @dataProvider processBlockDataProvider
     */
    public function testProcessBlock(
        $literal,
        $scheduleStructureCount,
        $ifconfigValue,
        $expectedConditions,
        $getCondition,
        $setCondition,
        $aclKey,
        $aclValue
    ) {
        $this->context->expects($this->once())->method('getScheduledStructure')
            ->will($this->returnValue($this->scheduledStructure));
        $this->scheduledStructure->expects($getCondition)
            ->method('getStructureElementData')
            ->with($literal, [])
            ->willReturn([
                'actions' => [
                    ['someMethod', [], 'action_config_path', 'scope'],
                ],
            ]);
        $this->scheduledStructure->expects($setCondition)
            ->method('setStructureElementData')
            ->with(
                $literal,
                [
                    'attributes' => [
                        Block::ATTRIBUTE_GROUP => '',
                        Block::ATTRIBUTE_CLASS => '',
                        Block::ATTRIBUTE_TEMPLATE => '',
                        Block::ATTRIBUTE_TTL => '',
                        Block::ATTRIBUTE_DISPLAY => '',
                        Block::ATTRIBUTE_ACL => $aclValue,
                        'visibilityConditions' => $expectedConditions,
                    ],
                    'actions' => [
                        ['someMethod', [], 'action_config_path', 'scope'],
                    ],
                    'arguments' => [],
                ]
            );

        $helper = $this->createMock(\Magento\Framework\View\Layout\ScheduledStructure\Helper::class);
        $helper->expects($scheduleStructureCount)->method('scheduleStructure')->will($this->returnValue($literal));

        $this->prepareReaderPool(
            '<' . $literal . ' ifconfig="' . $ifconfigValue . '" ' . $aclKey . '="' . $aclValue . '" >'
            . '<action method="someMethod" ifconfig="action_config_path" />'
            . '</' . $literal . '>',
            $literal
        );
        $objectManager = new ObjectManager($this);
        $condition = $objectManager->getObject(Condition::class);
        /** @var \Magento\Framework\View\Layout\Reader\Block $block */
        $block = $this->getBlock(
            [
                'helper' => $helper,
                'readerPool' => $this->readerPool,
                'conditionReader' => $condition,
                'scopeType' => 'scope',
            ]
        );
        $block->interpret($this->context, $this->currentElement);
    }

    /**
     * @return array
     */
    public function processBlockDataProvider()
    {
        return [
            [
                'block',
                $this->once(),
                '',
                [
                    'acl' => [
                        'name' => AclCondition::class,
                        'arguments' => [
                            'acl' => 'test'
                        ],
                    ],
                ],
                $this->once(),
                $this->once(),
                'acl',
                'test',
            ],
            [
                'block',
                $this->once(),
                'config_path',
                [
                    'acl' => [
                        'name' => AclCondition::class,
                        'arguments' => [
                            'acl' => 'test'
                        ],
                    ],
                    'ifconfig' => [
                        'name' => ConfigCondition::class,
                        'arguments' => [
                            'configPath' => 'config_path'
                        ],
                    ],
                ],
                $this->once(),
                $this->once(),
                'aclResource',
                'test',
            ],
            [
                'page',
                $this->never(),
                '',
                [
                    'acl' => [
                        'name' => AclCondition::class,
                        'arguments' => [
                            'acl' => 'test'
                        ],
                    ],
                    'ifconfig' => [
                        'name' => ConfigCondition::class,
                        'arguments' => [
                            'configPath' => 'config_path'
                        ],
                    ],
                ],
                $this->never(),
                $this->never(),
                'aclResource',
                'test',
            ],
        ];
    }

    /**
     * @param string $literal
     * @param string $remove
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $getCondition
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $setCondition
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $setRemoveCondition
     * @dataProvider processReferenceDataProvider
     */
    public function testProcessReference(
        $literal,
        $remove,
        $getCondition,
        $setCondition,
        $setRemoveCondition
    ) {
        if ($literal == 'referenceBlock' && $remove == 'false') {
            $this->scheduledStructure->expects($this->once())
                ->method('unsetElementFromListToRemove')
                ->with($literal);
        }

        $this->context->expects($this->once())->method('getScheduledStructure')
            ->will($this->returnValue($this->scheduledStructure));

        $this->scheduledStructure->expects($setRemoveCondition)
            ->method('setElementToRemoveList')
            ->with($literal);

        $this->scheduledStructure->expects($getCondition)
            ->method('getStructureElementData')
            ->with($literal, [])
            ->willReturn([
                'actions' => [
                    ['someMethod', [], 'action_config_path', 'scope'],
                ],
            ]);
        $this->scheduledStructure->expects($setCondition)
            ->method('setStructureElementData')
            ->with(
                $literal,
                [
                    'actions' => [
                        ['someMethod', [], 'action_config_path', 'scope'],
                    ],
                    'arguments' => [],
                    'attributes' => [
                        Block::ATTRIBUTE_GROUP => '',
                        Block::ATTRIBUTE_CLASS => '',
                        Block::ATTRIBUTE_TEMPLATE => '',
                        Block::ATTRIBUTE_TTL => '',
                        Block::ATTRIBUTE_DISPLAY => '',
                        Block::ATTRIBUTE_ACL => ''
                    ]
                ]
            );

        $this->prepareReaderPool(
            '<' . $literal . ' name="' . $literal . '" remove="' . $remove . '">'
            . '<action method="someMethod" ifconfig="action_config_path" />'
            . '</' . $literal . '>',
            $literal
        );
        $objectManager = new ObjectManager($this);
        $condition = $objectManager->getObject(Condition::class);
        /** @var \Magento\Framework\View\Layout\Reader\Block $block */
        $block = $this->getBlock(
            [
                'readerPool' => $this->readerPool,
                'conditionReader' => $condition,
                'scopeType' => 'scope',
            ]
        );
        $block->interpret($this->context, $this->currentElement);
    }

    /**
     * @return array
     */
    public function processReferenceDataProvider()
    {
        return [
            ['referenceBlock', 'false', $this->once(), $this->once(), $this->never()],
            ['referenceBlock', 'true', $this->never(), $this->never(), $this->once()],
            ['page', 'false', $this->never(), $this->never(), $this->never()],
            ['page', 'true', $this->never(), $this->never(), $this->never()],
        ];
    }
}
