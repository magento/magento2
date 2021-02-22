<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n\Dictionary\Writer;

class CsvTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    protected $_testFile;

    /**
     * @var \Magento\Setup\Module\I18n\Dictionary\Phrase|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_phraseFirstMock;

    /**
     * @var \Magento\Setup\Module\I18n\Dictionary\Phrase|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_phraseSecondMock;

    protected function setUp(): void
    {
        $this->_testFile = str_replace('\\', '/', realpath(dirname(__FILE__))) . '/_files/test.csv';

        $this->_phraseFirstMock = $this->createMock(\Magento\Setup\Module\I18n\Dictionary\Phrase::class);
        $this->_phraseSecondMock = $this->createMock(\Magento\Setup\Module\I18n\Dictionary\Phrase::class);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->_testFile)) {
            unlink($this->_testFile);
        }
    }

    /**
     */
    public function testWrongOutputFile()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot open file for write dictionary: "wrong/path"');

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $objectManagerHelper->getObject(
            \Magento\Setup\Module\I18n\Dictionary\Writer\Csv::class,
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

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Setup\Module\I18n\Dictionary\Writer\Csv $writer */
        $writer = $objectManagerHelper->getObject(
            \Magento\Setup\Module\I18n\Dictionary\Writer\Csv::class,
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

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Setup\Module\I18n\Dictionary\Writer\Csv $writer */
        $writer = $objectManagerHelper->getObject(
            \Magento\Setup\Module\I18n\Dictionary\Writer\Csv::class,
            ['outputFilename' => $this->_testFile]
        );
        $writer->write($this->_phraseFirstMock);
        $writer->write($this->_phraseSecondMock);

        $expected = "phrase1,translation1\nphrase2,translation2\n";
        $this->assertEquals($expected, file_get_contents($this->_testFile));
    }
}
