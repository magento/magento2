<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Framework\View\Layout\Reader\Block
 */
namespace Magento\Framework\View\Test\Unit\Layout\Reader;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout\AclCondition;
use Magento\Framework\View\Layout\ConfigCondition;
use Magento\Framework\View\Layout\Element;
use Magento\Framework\View\Layout\Reader\Block;
use Magento\Framework\View\Layout\Reader\Context;
use Magento\Framework\View\Layout\Reader\Visibility\Condition;
use Magento\Framework\View\Layout\ReaderPool;
use Magento\Framework\View\Layout\ScheduledStructure;
use Magento\Framework\View\Layout\ScheduledStructure\Helper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;

class BlockTest extends TestCase
{
    /**
     * @var ScheduledStructure|MockObject
     */
    protected $scheduledStructure;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var ReaderPool|MockObject
     */
    protected $readerPool;

    /**
     * @var Element
     */
    protected $currentElement;

    /**
     * @param string $xml
     * @param string $elementType
     * @return Element
     */
    protected function getElement($xml, $elementType)
    {
        $xml = '<' . Block::TYPE_BLOCK . '>'
            . $xml
            . '</' . Block::TYPE_BLOCK . '>';

        $xml = simplexml_load_string($xml, Element::class);
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
        return (new ObjectManager($this))
            ->getObject(Block::class, $arguments);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->scheduledStructure = $this->createMock(ScheduledStructure::class);
        $this->context = $this->createMock(Context::class);
        $this->readerPool = $this->createMock(ReaderPool::class);
    }

    /**
     * @param string $literal
     * @param InvokedCount $scheduleStructureCount
     * @param string $ifconfigValue
     * @param array $expectedConditions
     * @param InvokedCount $getCondition
     * @param InvokedCount $setCondition
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
            ->willReturn($this->scheduledStructure);
        $this->scheduledStructure->expects($getCondition)
            ->method('getStructureElementData')
            ->with($literal, [])
            ->willReturn(
                [
                    'actions' => [
                        ['someMethod', [], 'action_config_path', 'scope'],
                    ],
                ]
            );
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

        $helper = $this->createMock(Helper::class);
        $helper->expects($scheduleStructureCount)->method('scheduleStructure')->willReturn($literal);

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
     * @param InvokedCount $getCondition
     * @param InvokedCount $setCondition
     * @param InvokedCount $setRemoveCondition
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
            ->willReturn($this->scheduledStructure);

        $this->scheduledStructure->expects($setRemoveCondition)
            ->method('setElementToRemoveList')
            ->with($literal);

        $this->scheduledStructure->expects($getCondition)
            ->method('getStructureElementData')
            ->with($literal, [])
            ->willReturn(
                [
                    'actions' => [
                        ['someMethod', [], 'action_config_path', 'scope'],
                    ],
                ]
            );
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
