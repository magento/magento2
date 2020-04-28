<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n\Dictionary\Loader\File;

use Magento\Setup\Module\I18n\Dictionary;
use Magento\Setup\Module\I18n\Dictionary\Loader\File\AbstractFile;
use Magento\Setup\Module\I18n\Dictionary\Phrase;
use Magento\Setup\Module\I18n\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractFileTest extends TestCase
{
    /**
     * @var Dictionary|MockObject
     */
    protected $_dictionaryMock;

    /**
     * @var Factory|MockObject
     */
    protected $_factoryMock;

    /**
     * @var AbstractFile|MockObject
     */
    protected $_abstractLoaderMock;

    protected function setUp(): void
    {
        $this->_dictionaryMock = $this->createMock(Dictionary::class);
        $this->_factoryMock = $this->createMock(Factory::class);
    }

    public function testLoadWrongFile()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Cannot open dictionary file: "wrong_file.csv".');
        $abstractLoaderMock = $this->getMockForAbstractClass(
            AbstractFile::class,
            [],
            '',
            false
        );

        /** @var AbstractFile $abstractLoaderMock */
        $abstractLoaderMock->load('wrong_file.csv');
    }

    public function testLoad()
    {
        $abstractLoaderMock = $this->getMockForAbstractClass(
            AbstractFile::class,
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
        )->will(
            $this->returnValue(['phrase1', 'translation1'])
        );
        $abstractLoaderMock->expects(
            $this->at(2)
        )->method(
            '_readFile'
        )->will(
            $this->returnValue(['phrase2', 'translation2', 'context_type2', 'context_value2'])
        );

        $phraseFirstMock = $this->createMock(Phrase::class);
        $phraseSecondMock = $this->createMock(Phrase::class);

        $this->_factoryMock->expects(
            $this->once()
        )->method(
            'createDictionary'
        )->will(
            $this->returnValue($this->_dictionaryMock)
        );
        $this->_factoryMock->expects(
            $this->at(1)
        )->method(
            'createPhrase'
        )->with(
            ['phrase' => 'phrase1', 'translation' => 'translation1', 'context_type' => '', 'context_value' => '']
        )->will(
            $this->returnValue($phraseFirstMock)
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
        )->will(
            $this->returnValue($phraseSecondMock)
        );

        $this->_dictionaryMock->expects($this->at(0))->method('addPhrase')->with($phraseFirstMock);
        $this->_dictionaryMock->expects($this->at(1))->method('addPhrase')->with($phraseSecondMock);

        /** @var AbstractFile $abstractLoaderMock */
        $this->assertEquals($this->_dictionaryMock, $abstractLoaderMock->load('test.csv'));
    }

    public function testErrorsInPhraseCreating()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Invalid row #1: "exception_message".');
        $abstractLoaderMock = $this->getMockForAbstractClass(
            AbstractFile::class,
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
        )->will(
            $this->returnValue(['phrase1', 'translation1'])
        );

        $this->_factoryMock->expects(
            $this->once()
        )->method(
            'createDictionary'
        )->will(
            $this->returnValue($this->_dictionaryMock)
        );
        $this->_factoryMock->expects(
            $this->at(1)
        )->method(
            'createPhrase'
        )->will(
            $this->throwException(new \DomainException('exception_message'))
        );

        /** @var AbstractFile $abstractLoaderMock */
        $this->assertEquals($this->_dictionaryMock, $abstractLoaderMock->load('test.csv'));
    }
}
