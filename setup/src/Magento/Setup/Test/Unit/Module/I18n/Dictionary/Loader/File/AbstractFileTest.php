<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n\Dictionary\Loader\File;

class AbstractFileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Module\I18n\Dictionary|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dictionaryMock;

    /**
     * @var \Magento\Setup\Module\I18n\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryMock;

    /**
     * @var \Magento\Setup\Module\I18n\Dictionary\Loader\File\AbstractFile|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_abstractLoaderMock;

    protected function setUp()
    {
        $this->_dictionaryMock = $this->getMock('Magento\Setup\Module\I18n\Dictionary', [], [], '', false);
        $this->_factoryMock = $this->getMock('Magento\Setup\Module\I18n\Factory', [], [], '', false);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Cannot open dictionary file: "wrong_file.csv".
     */
    public function testLoadWrongFile()
    {
        $abstractLoaderMock = $this->getMockForAbstractClass(
            'Magento\Setup\Module\I18n\Dictionary\Loader\File\AbstractFile',
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
            'Magento\Setup\Module\I18n\Dictionary\Loader\File\AbstractFile',
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

        $phraseFirstMock = $this->getMock('Magento\Setup\Module\I18n\Dictionary\Phrase', [], [], '', false);
        $phraseSecondMock = $this->getMock('Magento\Setup\Module\I18n\Dictionary\Phrase', [], [], '', false);

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

        /** @var \Magento\Setup\Module\I18n\Dictionary\Loader\File\AbstractFile $abstractLoaderMock */
        $this->assertEquals($this->_dictionaryMock, $abstractLoaderMock->load('test.csv'));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Invalid row #1: "exception_message".
     */
    public function testErrorsInPhraseCreating()
    {
        $abstractLoaderMock = $this->getMockForAbstractClass(
            'Magento\Setup\Module\I18n\Dictionary\Loader\File\AbstractFile',
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

        /** @var \Magento\Setup\Module\I18n\Dictionary\Loader\File\AbstractFile $abstractLoaderMock */
        $this->assertEquals($this->_dictionaryMock, $abstractLoaderMock->load('test.csv'));
    }
}
