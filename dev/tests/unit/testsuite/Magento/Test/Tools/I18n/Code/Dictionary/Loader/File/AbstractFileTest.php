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
namespace Magento\Test\Tools\I18n\Code\Dictionary\Loader\File;

class AbstractFileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\I18n\Code\Dictionary|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dictionaryMock;

    /**
     * @var \Magento\Tools\I18n\Code\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryMock;

    /**
     * @var \Magento\Tools\I18n\Code\Dictionary\Loader\File\AbstractFile|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_abstractLoaderMock;

    protected function setUp()
    {
        $this->_dictionaryMock = $this->getMock('Magento\Tools\I18n\Code\Dictionary', array(), array(), '', false);
        $this->_factoryMock = $this->getMock('Magento\Tools\I18n\Code\Factory', array(), array(), '', false);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Cannot open dictionary file: "wrong_file.csv".
     */
    public function testLoadWrongFile()
    {
        $abstractLoaderMock = $this->getMockForAbstractClass(
            'Magento\Tools\I18n\Code\Dictionary\Loader\File\AbstractFile',
            array(),
            '',
            false
        );

        /** @var \Magento\Tools\I18n\Code\Dictionary\Loader\File\AbstractFile $abstractLoaderMock */
        $abstractLoaderMock->load('wrong_file.csv');
    }

    public function testLoad()
    {
        $abstractLoaderMock = $this->getMockForAbstractClass(
            'Magento\Tools\I18n\Code\Dictionary\Loader\File\AbstractFile',
            array($this->_factoryMock),
            '',
            true,
            true,
            true,
            array('_openFile', '_readFile', '_closeFile')
        );
        $abstractLoaderMock->expects(
            $this->at(1)
        )->method(
            '_readFile'
        )->will(
            $this->returnValue(array('phrase1', 'translation1'))
        );
        $abstractLoaderMock->expects(
            $this->at(2)
        )->method(
            '_readFile'
        )->will(
            $this->returnValue(array('phrase2', 'translation2', 'context_type2', 'context_value2'))
        );

        $phraseFirstMock = $this->getMock('Magento\Tools\I18n\Code\Dictionary\Phrase', array(), array(), '', false);
        $phraseSecondMock = $this->getMock('Magento\Tools\I18n\Code\Dictionary\Phrase', array(), array(), '', false);

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
            array('phrase' => 'phrase1', 'translation' => 'translation1', 'context_type' => '', 'context_value' => '')
        )->will(
            $this->returnValue($phraseFirstMock)
        );
        $this->_factoryMock->expects(
            $this->at(2)
        )->method(
            'createPhrase'
        )->with(
            array(
                'phrase' => 'phrase2',
                'translation' => 'translation2',
                'context_type' => 'context_type2',
                'context_value' => 'context_value2'
            )
        )->will(
            $this->returnValue($phraseSecondMock)
        );

        $this->_dictionaryMock->expects($this->at(0))->method('addPhrase')->with($phraseFirstMock);
        $this->_dictionaryMock->expects($this->at(1))->method('addPhrase')->with($phraseSecondMock);

        /** @var \Magento\Tools\I18n\Code\Dictionary\Loader\File\AbstractFile $abstractLoaderMock */
        $this->assertEquals($this->_dictionaryMock, $abstractLoaderMock->load('test.csv'));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Invalid row #1: "exception_message".
     */
    public function testErrorsInPhraseCreating()
    {
        $abstractLoaderMock = $this->getMockForAbstractClass(
            'Magento\Tools\I18n\Code\Dictionary\Loader\File\AbstractFile',
            array($this->_factoryMock),
            '',
            true,
            true,
            true,
            array('_openFile', '_readFile')
        );
        $abstractLoaderMock->expects(
            $this->at(1)
        )->method(
            '_readFile'
        )->will(
            $this->returnValue(array('phrase1', 'translation1'))
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

        /** @var \Magento\Tools\I18n\Code\Dictionary\Loader\File\AbstractFile $abstractLoaderMock */
        $this->assertEquals($this->_dictionaryMock, $abstractLoaderMock->load('test.csv'));
    }
}
