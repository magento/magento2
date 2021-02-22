<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n\Dictionary\Loader\File;

class AbstractFileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Module\I18n\Dictionary|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_dictionaryMock;

    /**
     * @var \Magento\Setup\Module\I18n\Factory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_factoryMock;

    /**
     * @var \Magento\Setup\Module\I18n\Dictionary\Loader\File\AbstractFile|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_abstractLoaderMock;

    protected function setUp(): void
    {
        $this->_dictionaryMock = $this->createMock(\Magento\Setup\Module\I18n\Dictionary::class);
        $this->_factoryMock = $this->createMock(\Magento\Setup\Module\I18n\Factory::class);
    }

    /**
     */
    public function testLoadWrongFile()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot open dictionary file: "wrong_file.csv".');

        $abstractLoaderMock = $this->getMockForAbstractClass(
            \Magento\Setup\Module\I18n\Dictionary\Loader\File\AbstractFile::class,
            [],
            '',
            false
        );

        /** @var \Magento\Setup\Module\I18n\Dictionary\Loader\File\AbstractFile $abstractLoaderMock */
        $abstractLoaderMock->load('wrong_file.csv');
    }

    public function testLoad()
    {
        $abstractLoaderMock = $this->getMockForAbstractClass(
            \Magento\Setup\Module\I18n\Dictionary\Loader\File\AbstractFile::class,
            [$this->_factoryMock],
            '',
            true,
            true,
            true,
            ['_openFile', '_readFile', '_closeFile']
        );
        $abstractLoaderMock->expects(
            $this->at(1)
        )->method(
            '_readFile'
        )->willReturn(
            ['phrase1', 'translation1']
        );
        $abstractLoaderMock->expects(
            $this->at(2)
        )->method(
            '_readFile'
        )->willReturn(
            ['phrase2', 'translation2', 'context_type2', 'context_value2']
        );

        $phraseFirstMock = $this->createMock(\Magento\Setup\Module\I18n\Dictionary\Phrase::class);
        $phraseSecondMock = $this->createMock(\Magento\Setup\Module\I18n\Dictionary\Phrase::class);

        $this->_factoryMock->expects(
            $this->once()
        )->method(
            'createDictionary'
        )->willReturn(
            $this->_dictionaryMock
        );
        $this->_factoryMock->expects(
            $this->at(1)
        )->method(
            'createPhrase'
        )->with(
            ['phrase' => 'phrase1', 'translation' => 'translation1', 'context_type' => '', 'context_value' => '']
        )->willReturn(
            $phraseFirstMock
        );
        $this->_factoryMock->expects(
            $this->at(2)
        )->method(
            'createPhrase'
        )->with(
            [
                'phrase' => 'phrase2',
                'translation' => 'translation2',
                'context_type' => 'context_type2',
                'context_value' => 'context_value2',
            ]
        )->willReturn(
            $phraseSecondMock
        );

        $this->_dictionaryMock->expects($this->at(0))->method('addPhrase')->with($phraseFirstMock);
        $this->_dictionaryMock->expects($this->at(1))->method('addPhrase')->with($phraseSecondMock);

        /** @var \Magento\Setup\Module\I18n\Dictionary\Loader\File\AbstractFile $abstractLoaderMock */
        $this->assertEquals($this->_dictionaryMock, $abstractLoaderMock->load('test.csv'));
    }

    /**
     */
    public function testErrorsInPhraseCreating()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid row #1: "exception_message".');

        $abstractLoaderMock = $this->getMockForAbstractClass(
            \Magento\Setup\Module\I18n\Dictionary\Loader\File\AbstractFile::class,
            [$this->_factoryMock],
            '',
            true,
            true,
            true,
            ['_openFile', '_readFile']
        );
        $abstractLoaderMock->expects(
            $this->at(1)
        )->method(
            '_readFile'
        )->willReturn(
            ['phrase1', 'translation1']
        );

        $this->_factoryMock->expects(
            $this->once()
        )->method(
            'createDictionary'
        )->willReturn(
            $this->_dictionaryMock
        );
        $this->_factoryMock->expects(
            $this->at(1)
        )->method(
            'createPhrase'
        )->will(
            $this->throwException(new \DomainException('exception_message'))
        );

        /** @var \Magento\Setup\Module\I18n\Dictionary\Loader\File\AbstractFile $abstractLoaderMock */
        $this->assertEquals($this->_dictionaryMock, $abstractLoaderMock->load('test.csv'));
    }
}
