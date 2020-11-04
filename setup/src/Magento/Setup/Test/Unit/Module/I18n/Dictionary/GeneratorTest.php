<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\I18n\Dictionary;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\I18n\Dictionary\Generator;
use Magento\Setup\Module\I18n\Dictionary\Options\Resolver;
use Magento\Setup\Module\I18n\Dictionary\Options\ResolverFactory;
use Magento\Setup\Module\I18n\Dictionary\Phrase;
use Magento\Setup\Module\I18n\Dictionary\WriterInterface;
use Magento\Setup\Module\I18n\Factory;
use Magento\Setup\Module\I18n\Parser\Contextual;
use Magento\Setup\Module\I18n\Parser\Parser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GeneratorTest extends TestCase
{
    /**
     * @var Parser|MockObject
     */
    protected $parserMock;

    /**
     * @var Contextual|MockObject
     */
    protected $contextualParserMock;

    /**
     * @var Factory|MockObject
     */
    protected $factoryMock;

    /**
     * @var WriterInterface|MockObject
     */
    protected $writerMock;

    /**
     * @var Generator
     */
    protected $generator;

    /**
     * @var ResolverFactory|MockObject
     */
    protected $optionsResolverFactory;

    protected function setUp(): void
    {
        $this->parserMock = $this->createMock(Parser::class);
        $this->contextualParserMock = $this->createMock(Contextual::class);
        $this->writerMock = $this->getMockForAbstractClass(WriterInterface::class);
        $this->factoryMock = $this->createMock(Factory::class);
        $this->factoryMock->expects($this->any())
            ->method('createDictionaryWriter')
            ->willReturn($this->writerMock);

        $this->optionsResolverFactory =
            $this->createMock(ResolverFactory::class);

        $objectManagerHelper = new ObjectManager($this);
        $this->generator = $objectManagerHelper->getObject(
            Generator::class,
            [
                'parser' => $this->parserMock,
                'contextualParser' => $this->contextualParserMock,
                'factory' => $this->factoryMock,
                'optionsResolver' => $this->optionsResolverFactory
            ]
        );
    }

    public function testCreatingDictionaryWriter()
    {
        $outputFilename = 'test';

        $phrase = $this->createMock(Phrase::class);
        $this->factoryMock->expects($this->once())
            ->method('createDictionaryWriter')
            ->with($outputFilename)->willReturnSelf();
        $this->parserMock->expects($this->any())->method('getPhrases')->willReturn([$phrase]);
        $options = [];
        $optionResolver = $this->createMock(Resolver::class);
        $optionResolver->expects($this->once())
            ->method('getOptions')
            ->willReturn($options);
        $this->optionsResolverFactory->expects($this->once())
            ->method('create')
            ->with('', false)
            ->willReturn($optionResolver);
        $this->generator->generate('', $outputFilename);
        $property = new \ReflectionProperty($this->generator, 'writer');
        $property->setAccessible(true);
        $this->assertNull($property->getValue($this->generator));
    }

    public function testUsingRightParserWhileWithoutContextParsing()
    {
        $baseDir = 'right_parser';
        $outputFilename = 'file.csv';
        $filesOptions = ['file1', 'file2'];
        $optionResolver =
            $this->createMock(Resolver::class);
        $optionResolver->expects($this->once())
            ->method('getOptions')
            ->willReturn($filesOptions);

        $this->factoryMock->expects($this->once())
            ->method('createDictionaryWriter')
            ->with($outputFilename)->willReturnSelf();

        $this->optionsResolverFactory->expects($this->once())
            ->method('create')
            ->with($baseDir, false)
            ->willReturn($optionResolver);
        $this->parserMock->expects($this->once())->method('parse')->with($filesOptions);
        $phrase = $this->createMock(Phrase::class);
        $this->parserMock->expects($this->once())->method('getPhrases')->willReturn([$phrase]);
        $this->generator->generate($baseDir, $outputFilename);
    }

    public function testUsingRightParserWhileWithContextParsing()
    {
        $baseDir = 'right_parser2';
        $outputFilename = 'file.csv';
        $filesOptions = ['file1', 'file2'];
        $optionResolver =
            $this->createMock(Resolver::class);
        $optionResolver->expects($this->once())
            ->method('getOptions')
            ->willReturn($filesOptions);
        $this->optionsResolverFactory->expects($this->once())
            ->method('create')
            ->with($baseDir, true)
            ->willReturn($optionResolver);

        $this->contextualParserMock->expects($this->once())->method('parse')->with($filesOptions);
        $phrase = $this->createMock(Phrase::class);
        $this->contextualParserMock->expects($this->once())->method('getPhrases')->willReturn([$phrase]);

        $this->factoryMock->expects($this->once())
            ->method('createDictionaryWriter')
            ->with($outputFilename)->willReturnSelf();

        $this->generator->generate($baseDir, $outputFilename, true);
    }

    public function testWritingPhrases()
    {
        $baseDir = 'WritingPhrases';
        $filesOptions = ['file1', 'file2'];
        $optionResolver =
            $this->createMock(Resolver::class);
        $optionResolver->expects($this->once())
            ->method('getOptions')
            ->willReturn($filesOptions);
        $this->optionsResolverFactory->expects($this->once())
            ->method('create')
            ->with($baseDir, false)
            ->willReturn($optionResolver);

        $phrases = [
            $this->createMock(Phrase::class),
            $this->createMock(Phrase::class),
        ];

        $this->parserMock->expects($this->once())->method('getPhrases')->willReturn($phrases);
        $this->writerMock->expects($this->at(0))->method('write')->with($phrases[0]);
        $this->writerMock->expects($this->at(1))->method('write')->with($phrases[1]);

        $this->generator->generate($baseDir, 'file.csv');
    }

    public function testGenerateWithNoPhrases()
    {
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('No phrases found in the specified dictionary file.');
        $baseDir = 'no_phrases';
        $outputFilename = 'no_file.csv';
        $filesOptions = ['file1', 'file2'];
        $optionResolver =
            $this->createMock(Resolver::class);
        $optionResolver->expects($this->once())
            ->method('getOptions')
            ->willReturn($filesOptions);
        $this->optionsResolverFactory->expects($this->once())
            ->method('create')
            ->with($baseDir, true)
            ->willReturn($optionResolver);

        $this->contextualParserMock->expects($this->once())->method('parse')->with($filesOptions);
        $this->contextualParserMock->expects($this->once())->method('getPhrases')->willReturn([]);
        $this->generator->generate($baseDir, $outputFilename, true);
    }
}
