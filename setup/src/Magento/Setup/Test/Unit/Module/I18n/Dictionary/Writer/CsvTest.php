<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\I18n\Dictionary\Writer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\I18n\Dictionary\Phrase;
use Magento\Setup\Module\I18n\Dictionary\Writer\Csv;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CsvTest extends TestCase
{
    /**
     * @var string
     */
    protected $_testFile;

    /**
     * @var Phrase|MockObject
     */
    protected $_phraseFirstMock;

    /**
     * @var Phrase|MockObject
     */
    protected $_phraseSecondMock;

    protected function setUp(): void
    {
        $this->_testFile = str_replace('\\', '/', realpath(dirname(__FILE__))) . '/_files/test.csv';

        $this->_phraseFirstMock = $this->createMock(Phrase::class);
        $this->_phraseSecondMock = $this->createMock(Phrase::class);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->_testFile)) {
            unlink($this->_testFile);
        }
    }

    public function testWrongOutputFile()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Cannot open file for write dictionary: "wrong/path"');
        $objectManagerHelper = new ObjectManager($this);
        $objectManagerHelper->getObject(
            Csv::class,
            ['outputFilename' => 'wrong/path']
        );
    }

    public function testWrite()
    {
        $this->_phraseFirstMock->expects(
            $this->once()
        )->method(
            'getCompiledPhrase'
        )->willReturn(
            "phrase1_quote'"
        );
        $this->_phraseFirstMock->expects(
            $this->once()
        )->method(
            'getCompiledTranslation'
        )->willReturn(
            "translation1_quote'"
        );
        $this->_phraseFirstMock->expects(
            $this->once()
        )->method(
            'getContextType'
        )->willReturn(
            "context_type1_quote\\'"
        );
        $this->_phraseFirstMock->expects(
            $this->once()
        )->method(
            'getContextValueAsString'
        )->willReturn(
            "content_value1_quote\\'"
        );

        $this->_phraseSecondMock->expects(
            $this->once()
        )->method(
            'getCompiledPhrase'
        )->willReturn(
            "phrase2_quote'"
        );
        $this->_phraseSecondMock->expects(
            $this->once()
        )->method(
            'getCompiledTranslation'
        )->willReturn(
            "translation2_quote'"
        );
        $this->_phraseSecondMock->expects(
            $this->once()
        )->method(
            'getContextType'
        )->willReturn(
            "context_type2_quote\\'"
        );
        $this->_phraseSecondMock->expects(
            $this->once()
        )->method(
            'getContextValueAsString'
        )->willReturn(
            "content_value2_quote\\'"
        );

        $objectManagerHelper = new ObjectManager($this);
        /** @var Csv $writer */
        $writer = $objectManagerHelper->getObject(
            Csv::class,
            ['outputFilename' => $this->_testFile]
        );
        $writer->write($this->_phraseFirstMock);
        $writer->write($this->_phraseSecondMock);

        $expected = <<<EXPECTED
phrase1_quote',translation1_quote',"context_type1_quote\'","content_value1_quote\'"
phrase2_quote',translation2_quote',"context_type2_quote\'","content_value2_quote\'"

EXPECTED;

        $this->assertEquals($expected, file_get_contents($this->_testFile));
    }

    public function testWriteWithoutContext()
    {
        $this->_phraseFirstMock->expects($this->once())
            ->method('getCompiledPhrase')
            ->willReturn('phrase1');
        $this->_phraseFirstMock->expects($this->once())
            ->method('getCompiledTranslation')
            ->willReturn('translation1');
        $this->_phraseFirstMock->expects($this->once())->method('getContextType')->willReturn('');

        $this->_phraseSecondMock->expects($this->once())
            ->method('getCompiledPhrase')
            ->willReturn('phrase2');
        $this->_phraseSecondMock->expects($this->once())
            ->method('getCompiledTranslation')
            ->willReturn('translation2');
        $this->_phraseSecondMock->expects($this->once())
            ->method('getContextType')
            ->willReturn('context_type2');
        $this->_phraseSecondMock->expects($this->once())
            ->method('getContextValueAsString')
            ->willReturn('');

        $objectManagerHelper = new ObjectManager($this);
        /** @var Csv $writer */
        $writer = $objectManagerHelper->getObject(
            Csv::class,
            ['outputFilename' => $this->_testFile]
        );
        $writer->write($this->_phraseFirstMock);
        $writer->write($this->_phraseSecondMock);

        $expected = "phrase1,translation1\nphrase2,translation2\n";
        $this->assertEquals($expected, file_get_contents($this->_testFile));
    }
}
