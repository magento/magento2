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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Tools\I18n\Code\Dictionary;

/**
 * Generator test
 */
class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\I18n\Code\Parser\Parser|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $parserMock;

    /**
     * @var \Magento\Tools\I18n\Code\Parser\Contextual|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextualParserMock;

    /**
     * @var \Magento\Tools\I18n\Code\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $factoryMock;

    /**
     * @var \Magento\Tools\I18n\Code\Dictionary\WriterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $writerMock;

    /**
     * @var \Magento\Tools\I18n\Code\Dictionary\Generator
     */
    protected $generator;

    /**
     * @var \Magento\Tools\I18n\Code\Dictionary\Options\ResolverFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionsResolverFactory;

    protected function setUp()
    {
        $this->parserMock = $this->getMock('Magento\Tools\I18n\Code\Parser\Parser', array(), array(), '', false);
        $this->contextualParserMock = $this->getMock(
            'Magento\Tools\I18n\Code\Parser\Contextual',
            array(),
            array(),
            '',
            false
        );
        $this->writerMock = $this->getMock('Magento\Tools\I18n\Code\Dictionary\WriterInterface');
        $this->factoryMock = $this->getMock('Magento\Tools\I18n\Code\Factory', array(), array(), '', false);
        $this->factoryMock->expects($this->any())
            ->method('createDictionaryWriter')
            ->will($this->returnValue($this->writerMock));

        $this->optionsResolverFactory = $this->getMock(
            'Magento\Tools\I18n\Code\Dictionary\Options\ResolverFactory',
            [],
            [],
            '',
            false
        );

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->generator = $objectManagerHelper->getObject(
            'Magento\Tools\I18n\Code\Dictionary\Generator',
            array(
                'parser' => $this->parserMock,
                'contextualParser' => $this->contextualParserMock,
                'factory' => $this->factoryMock,
                'optionsResolver' => $this->optionsResolverFactory
            )
        );
    }

    public function testCreatingDictionaryWriter()
    {
        $outputFilename = 'test';

        $phrase = $this->getMock('Magento\Tools\I18n\Code\Dictionary\Phrase', [], [], '', false);
        $this->factoryMock->expects($this->once())
            ->method('createDictionaryWriter')
            ->with($outputFilename)
            ->will($this->returnSelf());
        $this->parserMock->expects($this->any())->method('getPhrases')->will($this->returnValue(array($phrase)));
        $options = [];
        $optionResolver = $this->getMock('Magento\Tools\I18n\Code\Dictionary\Options\Resolver', [], [], '', false);
        $optionResolver->expects($this->once())
            ->method('getOptions')
            ->will($this->returnValue($options));
        $this->optionsResolverFactory->expects($this->once())
            ->method('create')
            ->with($this->equalTo(''), $this->equalTo(false))
            ->will($this->returnValue($optionResolver));
        $this->generator->generate('', $outputFilename);
        $property = new \ReflectionProperty($this->generator, 'writer');
        $property->setAccessible(true);
        $this->assertNull($property->getValue($this->generator));
    }

    public function testUsingRightParserWhileWithoutContextParsing()
    {
        $baseDir = 'right_parser';
        $outputFilename = 'file.csv';
        $filesOptions = array('file1', 'file2');
        $optionResolver = $this->getMock('Magento\Tools\I18n\Code\Dictionary\Options\Resolver', [], [], '', false);
        $optionResolver->expects($this->once())
            ->method('getOptions')
            ->will($this->returnValue($filesOptions));

        $this->factoryMock->expects($this->once())
            ->method('createDictionaryWriter')
            ->with($outputFilename)
            ->will($this->returnSelf());

        $this->optionsResolverFactory->expects($this->once())
            ->method('create')
            ->with($this->equalTo($baseDir), $this->equalTo(false))
            ->will($this->returnValue($optionResolver));
        $this->parserMock->expects($this->once())->method('parse')->with($filesOptions);
        $phrase = $this->getMock('Magento\Tools\I18n\Code\Dictionary\Phrase', [], [], '', false);
        $this->parserMock->expects($this->once())->method('getPhrases')->will($this->returnValue(array($phrase)));
        $this->generator->generate($baseDir, $outputFilename);
    }

    public function testUsingRightParserWhileWithContextParsing()
    {
        $baseDir = 'right_parser2';
        $outputFilename = 'file.csv';
        $filesOptions = array('file1', 'file2');
        $optionResolver = $this->getMock('Magento\Tools\I18n\Code\Dictionary\Options\Resolver', [], [], '', false);
        $optionResolver->expects($this->once())
            ->method('getOptions')
            ->will($this->returnValue($filesOptions));
        $this->optionsResolverFactory->expects($this->once())
            ->method('create')
            ->with($this->equalTo($baseDir), $this->equalTo(true))
            ->will($this->returnValue($optionResolver));

        $this->contextualParserMock->expects($this->once())->method('parse')->with($filesOptions);
        $phrase = $this->getMock('Magento\Tools\I18n\Code\Dictionary\Phrase', [], [], '', false);
        $this->contextualParserMock->expects($this->once())->method('getPhrases')->will($this->returnValue([$phrase]));

        $this->factoryMock->expects($this->once())
            ->method('createDictionaryWriter')
            ->with($outputFilename)
            ->will($this->returnSelf());

        $this->generator->generate($baseDir, $outputFilename, true);
    }

    public function testWritingPhrases()
    {
        $baseDir = 'WritingPhrases';
        $filesOptions = array('file1', 'file2');
        $optionResolver = $this->getMock('Magento\Tools\I18n\Code\Dictionary\Options\Resolver', [], [], '', false);
        $optionResolver->expects($this->once())
            ->method('getOptions')
            ->will($this->returnValue($filesOptions));
        $this->optionsResolverFactory->expects($this->once())
            ->method('create')
            ->with($this->equalTo($baseDir), $this->equalTo(false))
            ->will($this->returnValue($optionResolver));

        $phrases = array(
            $this->getMock('Magento\Tools\I18n\Code\Dictionary\Phrase', array(), array(), '', false),
            $this->getMock('Magento\Tools\I18n\Code\Dictionary\Phrase', array(), array(), '', false)
        );

        $this->parserMock->expects($this->once())->method('getPhrases')->will($this->returnValue($phrases));
        $this->writerMock->expects($this->at(0))->method('write')->with($phrases[0]);
        $this->writerMock->expects($this->at(1))->method('write')->with($phrases[1]);

        $this->generator->generate($baseDir, 'file.csv');
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage No phrases found in the specified dictionary file.
     */
    public function testGenerateWithNoPhrases()
    {
        $baseDir = 'no_phrases';
        $outputFilename = 'no_file.csv';
        $filesOptions = array('file1', 'file2');
        $optionResolver = $this->getMock('Magento\Tools\I18n\Code\Dictionary\Options\Resolver', [], [], '', false);
        $optionResolver->expects($this->once())
            ->method('getOptions')
            ->will($this->returnValue($filesOptions));
        $this->optionsResolverFactory->expects($this->once())
            ->method('create')
            ->with($this->equalTo($baseDir), $this->equalTo(true))
            ->will($this->returnValue($optionResolver));

        $this->contextualParserMock->expects($this->once())->method('parse')->with($filesOptions);
        $this->contextualParserMock->expects($this->once())->method('getPhrases')->will($this->returnValue([]));
        $this->generator->generate($baseDir, $outputFilename, true);
    }
}
