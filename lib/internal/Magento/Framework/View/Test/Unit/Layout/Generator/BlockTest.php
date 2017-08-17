<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Layout\Generator;

/**
 * @covers \Magento\Framework\View\Layout\Generator\Block
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BlockTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Magento\Framework\View\Layout\Generator\Block::process()
     * @covers \Magento\Framework\View\Layout\Generator\Block::createBlock()
     * @param string $testGroup
     * @param string $testTemplate
     * @param string $testTtl
     * @param array $testArgumentData
     * @param bool $testIsFlag
     * @param bool $isNeedEvaluate
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $addToParentGroupCount
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $setTemplateCount
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $setTtlCount
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $setIsFlag
     * @dataProvider provider
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function testProcess(
        $testGroup,
        $testTemplate,
        $testTtl,
        $testArgumentData,
        $testIsFlag,
        $isNeedEvaluate,
        $addToParentGroupCount,
        $setTemplateCount,
        $setTtlCount,
        $setIsFlag
    ) {
        $elementName = 'test_block';
        $methodName = 'setTest';
        $literal = 'block';
        $argumentData = ['argument' => 'value'];
        $class = 'test_class';

        $scheduleStructure = $this->createMock(\Magento\Framework\View\Layout\ScheduledStructure::class);
        $scheduleStructure->expects($this->once())->method('getElements')->will(
            $this->returnValue(
                [
                    $elementName => [
                        $literal,
                        [
                            'actions' => [
                                [
                                    $methodName,
                                    [
                                        'test_argument' => $argumentData
                                    ],
                                    'config_path',
                                    'scope',
                                ],
                            ]
                        ],
                    ],
                ]
            )
        );

        $scheduleStructure->expects($this->once())->method('getElement')->with($elementName)->will(
            $this->returnValue(
                [
                    '',
                    [
                        'attributes' => [
                            'class' => $class,
                            'template' => $testTemplate,
                            'ttl' => $testTtl,
                            'group' => $testGroup,
                        ],
                        'arguments' => $testArgumentData
                    ],
                ]
            )
        );
        $scheduleStructure->expects($this->once())->method('unsetElement')->with($elementName);

        /**
         * @var \Magento\Framework\View\Layout\Reader\Context|\PHPUnit_Framework_MockObject_MockObject $readerContext
         */
        $readerContext = $this->createMock(\Magento\Framework\View\Layout\Reader\Context::class);
        $readerContext->expects($this->once())->method('getScheduledStructure')
            ->will($this->returnValue($scheduleStructure));

        $layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);

        /**
         * @var \Magento\Framework\View\Element\AbstractBlock|\PHPUnit_Framework_MockObject_MockObject $blockInstance
         */
        // explicitly set mocked methods for successful expectation of magic methods
        $blockInstance = $this->createPartialMock(
            \Magento\Framework\View\Element\AbstractBlock::class,
            ['setType', 'setTemplate', 'setTtl', $methodName, 'setNameInLayout', 'addData', 'setLayout']
        );
        $blockInstance->expects($this->once())->method('setType')->with(get_class($blockInstance));
        $blockInstance->expects($this->once())->method('setNameInLayout')->with($elementName);
        $blockInstance->expects($this->once())->method('addData')->with($argumentData);
        $blockInstance->expects($setTemplateCount)->method('setTemplate')->with($testTemplate);
        $blockInstance->expects($setTtlCount)->method('setTtl')->with(0);
        $blockInstance->expects($this->once())->method('setLayout')->with($layout);
        $blockInstance->expects($setIsFlag)->method($methodName)->with($argumentData);

        $layout->expects($this->once())->method('setBlock')->with($elementName, $blockInstance);

        $structure = $this->createMock(\Magento\Framework\View\Layout\Data\Structure::class);
        $structure->expects($addToParentGroupCount)->method('addToParentGroup')->with($elementName, $testGroup);

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject $generatorContext
         */
        $generatorContext = $this->createMock(\Magento\Framework\View\Layout\Generator\Context::class);
        $generatorContext->expects($this->once())->method('getLayout')->will($this->returnValue($layout));
        $generatorContext->expects($this->once())->method('getStructure')->will($this->returnValue($structure));

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject $argumentInterpreter
         */
        $argumentInterpreter = $this->createMock(\Magento\Framework\Data\Argument\InterpreterInterface::class);
        if ($isNeedEvaluate) {
            $argumentInterpreter
                ->expects($this->any())
                ->method('evaluate')
                ->with($testArgumentData['argument'])
                ->willReturn($argumentData['argument']);
        } else {
            $argumentInterpreter->expects($this->never())->method('evaluate')
            ;
        }

        /** @var \Magento\Framework\View\Element\BlockFactory|\PHPUnit_Framework_MockObject_MockObject $blockFactory */
        $blockFactory = $this->createMock(\Magento\Framework\View\Element\BlockFactory::class);
        $blockFactory->expects($this->any())
            ->method('createBlock')
            ->with($class, ['data' => $argumentData])
            ->will($this->returnValue($blockInstance));

        /** @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject $eventManager */
        $eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $eventManager->expects($this->once())->method('dispatch')
            ->with('core_layout_block_create_after', [$literal => $blockInstance]);

        $scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $scopeConfigMock->expects($this->once())->method('isSetFlag')
            ->with('config_path', 'scope', 'default')->willReturn($testIsFlag);

        $scopeResolverMock = $this->createMock(\Magento\Framework\App\ScopeResolverInterface::class);
        $scopeResolverMock->expects($this->once())->method('getScope')
            ->willReturn('default');

        /** @var \Magento\Framework\View\Layout\Generator\Block $block */
        $block = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(
                \Magento\Framework\View\Layout\Generator\Block::class,
                [
                    'argumentInterpreter' => $argumentInterpreter,
                    'blockFactory' => $blockFactory,
                    'eventManager' => $eventManager,
                    'scopeConfig' => $scopeConfigMock,
                    'scopeResolver' => $scopeResolverMock,
                ]
            );
        $block->process($readerContext, $generatorContext);
    }

    /**
     * @return array
     */
    public function provider()
    {
        return [
            [
                'test_group',
                '',
                'testTtl',
                ['argument' => ['name' => 'argument', 'xsi:type' => 'type', 'value' => 'value']],
                true,
                true,
                $this->once(),
                $this->never(),
                $this->once(),
                $this->once(),
            ],
            [
                '',
                'test_template',
                '',
                ['argument' => 'value'],
                false,
                false,
                $this->never(),
                $this->once(),
                $this->never(),
                $this->never(),
            ],
        ];
    }
}
