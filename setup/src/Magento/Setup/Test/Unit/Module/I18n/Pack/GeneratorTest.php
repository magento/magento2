<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n\Pack;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\I18n\Dictionary;
use Magento\Setup\Module\I18n\Dictionary\Loader\FileInterface;
use Magento\Setup\Module\I18n\Dictionary\Phrase;
use Magento\Setup\Module\I18n\Factory;
use Magento\Setup\Module\I18n\Locale;
use Magento\Setup\Module\I18n\Pack\Generator;
use Magento\Setup\Module\I18n\Pack\WriterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GeneratorTest extends TestCase
{
    /**
     * @var FileInterface|MockObject
     */
    protected $dictionaryLoaderMock;

    /**
     * @var WriterInterface|MockObject
     */
    protected $packWriterMock;

    /**
     * @var Factory|MockObject
     */
    protected $factoryMock;

    /**
     * @var Dictionary|MockObject
     */
    protected $dictionaryMock;

    /**
     * @var Generator
     */
    protected $_generator;

    protected function setUp(): void
    {
        $this->dictionaryLoaderMock =
            $this->createMock(FileInterface::class);
        $this->packWriterMock = $this->createMock(WriterInterface::class);
        $this->factoryMock = $this->createMock(Factory::class);
        $this->dictionaryMock = $this->createMock(Dictionary::class);

        $objectManagerHelper = new ObjectManager($this);
        $this->_generator = $objectManagerHelper->getObject(
            Generator::class,
            [
                'dictionaryLoader' => $this->dictionaryLoaderMock,
                'packWriter' => $this->packWriterMock,
                'factory' => $this->factoryMock
            ]
        );
    }

    public function testGenerate()
    {
        $dictionaryPath = 'dictionary_path';
        $localeString = 'locale';
        $mode = 'mode';
        $allowDuplicates = true;
        $localeMock = $this->createMock(Locale::class);

        $phrases = [$this->createMock(Phrase::class)];
        $this->dictionaryMock->expects($this->once())
            ->method('getPhrases')
            ->will($this->returnValue([$phrases]));

        $this->factoryMock->expects($this->once())
            ->method('createLocale')
            ->with($localeString)
            ->will($this->returnValue($localeMock));
        $this->dictionaryLoaderMock->expects($this->once())
            ->method('load')
            ->with($dictionaryPath)
            ->will($this->returnValue($this->dictionaryMock));
        $this->packWriterMock->expects($this->once())
            ->method('writeDictionary')
            ->with($this->dictionaryMock, $localeMock, $mode);

        $this->_generator->generate($dictionaryPath, $localeString, $mode, $allowDuplicates);
    }

    public function testGenerateEmptyFile()
    {
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('No phrases have been found by the specified path.');
        $dictionaryPath = 'dictionary_path';
        $localeString = 'locale';
        $mode = 'mode';
        $allowDuplicates = true;
        $localeMock = $this->createMock(Locale::class);

        $this->factoryMock->expects($this->once())
            ->method('createLocale')
            ->with($localeString)
            ->will($this->returnValue($localeMock));
        $this->dictionaryLoaderMock->expects($this->once())
            ->method('load')
            ->with($dictionaryPath)
            ->will($this->returnValue($this->dictionaryMock));
        $this->dictionaryMock->expects($this->once())
            ->method('getPhrases')
            ->will($this->returnValue([]));

        $this->_generator->generate($dictionaryPath, $localeString, $mode, $allowDuplicates);
    }

    public function testGenerateWithNotAllowedDuplicatesAndDuplicatesExist()
    {
        $error = "Duplicated translation is found, but it is not allowed.\n"
            . "The phrase \"phrase1\" is translated in 1 places.\n"
            . "The phrase \"phrase2\" is translated in 1 places.\n";
        $this->expectException('\RuntimeException');
        $this->expectExceptionMessage($error);

        $allowDuplicates = false;

        $phraseFirstMock = $this->createMock(Phrase::class);
        $phraseFirstMock->expects($this->once())->method('getPhrase')->will($this->returnValue('phrase1'));
        $phraseSecondMock = $this->createMock(Phrase::class);
        $phraseSecondMock->expects($this->once())->method('getPhrase')->will($this->returnValue('phrase2'));

        $this->dictionaryLoaderMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue($this->dictionaryMock));
        $phrases = [$this->createMock(Phrase::class)];
        $this->dictionaryMock->expects($this->once())
            ->method('getPhrases')
            ->will($this->returnValue([$phrases]));
        $this->dictionaryMock->expects($this->once())
            ->method('getDuplicates')
            ->will($this->returnValue([[$phraseFirstMock], [$phraseSecondMock]]));

        $this->_generator->generate('dictionary_path', 'locale', 'mode', $allowDuplicates);
    }
}
