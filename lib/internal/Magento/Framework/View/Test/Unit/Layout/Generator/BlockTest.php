<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Layout\Generator;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Framework\View\Layout\Data\Structure;
use Magento\Framework\View\Layout\Generator\Block;
use Magento\Framework\View\Layout\Reader\Context;
use Magento\Framework\View\Layout\ScheduledStructure;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @covers \Magento\Framework\View\Layout\Generator\Block
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BlockTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @covers \Magento\Framework\View\Layout\Generator\Block::process()
     * @covers \Magento\Framework\View\Layout\Generator\Block::createBlock()
     * @param string $testGroup
     * @param string $testTemplate
     * @param string $testTtl
     * @param array $testArgumentData
     * @param bool $testIsFlag
     * @param bool $isNeedEvaluate
     * @param InvokedCount $addToParentGroupCount
     * @param InvokedCount $setTemplateCount
     * @param InvokedCount $setTtlCount
     * @param InvokedCount $setIsFlag     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    #[DataProvider('provider')]
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
        // Convert string expectations to matchers
        $addToParentGroupCount = is_string($addToParentGroupCount) 
            ? $this->createInvocationMatcher($addToParentGroupCount) 
            : $addToParentGroupCount;
        $setTemplateCount = is_string($setTemplateCount) 
            ? $this->createInvocationMatcher($setTemplateCount) 
            : $setTemplateCount;
        $setTtlCount = is_string($setTtlCount) 
            ? $this->createInvocationMatcher($setTtlCount) 
            : $setTtlCount;
        $setIsFlag = is_string($setIsFlag) 
            ? $this->createInvocationMatcher($setIsFlag) 
            : $setIsFlag;
        
        $elementName = 'test_block';
        $methodName = 'setTest';
        $literal = 'block';
        $argumentData = ['argument' => 'value'];
        $class = 'test_class';

        $scheduleStructure = $this->createMock(ScheduledStructure::class);
        $scheduleStructure->expects($this->once())->method('getElements')->willReturn(
            [
                $elementName => [
                    $literal,
                    [
                        'actions' => [
                            [
                                $methodName,
                                [$argumentData],
                                'config_path',
                                'scope',
                            ],
                        ]
                    ],
                ],
            ]
        );

        $scheduleStructure->expects($this->once())->method('getElement')->with($elementName)->willReturn(
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
        );
        $scheduleStructure->expects($this->once())->method('unsetElement')->with($elementName);

        /**
         * @var Context|MockObject $readerContext
         */
        $readerContext = $this->createMock(Context::class);
        $readerContext->expects($this->once())->method('getScheduledStructure')
            ->willReturn($scheduleStructure);

        $layout = $this->createMock(LayoutInterface::class);

        /**
         * @var \Magento\Framework\View\Element\AbstractBlock|\PHPUnit\Framework\MockObject\MockObject $blockInstance
         */
        // Use createPartialMockWithReflection because setType doesn't exist in AbstractBlock
        $blockInstance = $this->createPartialMockWithReflection(
            AbstractBlock::class,
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

        $structure = $this->createMock(Structure::class);
        $structure->expects($addToParentGroupCount)->method('addToParentGroup')->with($elementName, $testGroup);

        /**
         * @var MockObject $generatorContext
         */
        $generatorContext = $this->createMock(\Magento\Framework\View\Layout\Generator\Context::class);
        $generatorContext->expects($this->once())->method('getLayout')->willReturn($layout);
        $generatorContext->expects($this->once())->method('getStructure')->willReturn($structure);

        /**
         * @var MockObject $argumentInterpreter
         */
        $argumentInterpreter = $this->createMock(InterpreterInterface::class);
        if ($isNeedEvaluate) {
            $argumentInterpreter
                ->expects($this->any())
                ->method('evaluate')
                ->with($testArgumentData['argument'])
                ->willReturn($argumentData['argument']);
        } else {
            $argumentInterpreter->expects($this->never())->method('evaluate');
        }

        /** @var BlockFactory|MockObject $blockFactory */
        $blockFactory = $this->createMock(BlockFactory::class);
        $blockFactory->expects($this->any())
            ->method('createBlock')
            ->with($class, ['data' => $argumentData])
            ->willReturn($blockInstance);

        /** @var ManagerInterface|MockObject $eventManager */
        $eventManager = $this->createMock(ManagerInterface::class);
        $eventManager->expects($this->once())->method('dispatch')
            ->with('core_layout_block_create_after', [$literal => $blockInstance]);

        $scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $scopeConfigMock->expects($this->once())->method('isSetFlag')
            ->with('config_path', 'scope', 'default')->willReturn($testIsFlag);

        $scopeResolverMock = $this->createMock(ScopeResolverInterface::class);
        $scopeResolverMock->expects($this->once())->method('getScope')
            ->willReturn('default');

        /** @var Block $block */
        $block = (new ObjectManager($this))
            ->getObject(
                Block::class,
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
    public static function provider()
    {
        return [
            [
                'test_group',
                '',
                'testTtl',
                ['argument' => ['name' => 'argument', 'xsi:type' => 'type', 'value' => 'value']],
                true,
                true,
                'once',
                'never',
                'once',
                'once',
            ],
            [
                '',
                'test_template',
                '',
                ['argument' => 'value'],
                false,
                false,
                'never',
                'once',
                'never',
                'never',
            ],
        ];
    }
}
