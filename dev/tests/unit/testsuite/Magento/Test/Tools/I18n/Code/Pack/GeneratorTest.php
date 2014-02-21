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

namespace Magento\Test\Tools\I18n\Code\Pack;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\I18n\Code\Dictionary\Loader\FileInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dictionaryLoaderMock;

    /**
     * @var \Magento\Tools\I18n\Code\Pack\WriterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_packWriterMock;

    /**
     * @var \Magento\Tools\I18n\Code\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryMock;

    /**
     * @var \Magento\Tools\I18n\Code\Dictionary|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dictionaryMock;

    /**
     * @var \Magento\Tools\I18n\Code\Pack\Generator
     */
    protected $_generator;

    protected function setUp()
    {
        $this->_dictionaryLoaderMock = $this->getMock('Magento\Tools\I18n\Code\Dictionary\Loader\FileInterface');
        $this->_packWriterMock = $this->getMock('Magento\Tools\I18n\Code\Pack\WriterInterface');
        $this->_factoryMock = $this->getMock('Magento\Tools\I18n\Code\Factory', array(), array(), '', false);
        $this->_dictionaryMock = $this->getMock('Magento\Tools\I18n\Code\Dictionary', array(), array(), '', false);

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_generator = $objectManagerHelper->getObject('Magento\Tools\I18n\Code\Pack\Generator', array(
            'dictionaryLoader' => $this->_dictionaryLoaderMock,
            'packWriter' => $this->_packWriterMock,
            'factory' => $this->_factoryMock,
        ));
    }

    public function testGenerate()
    {
        $dictionaryPath = 'dictionary_path';
        $packPath = 'pack_path';
        $localeString = 'locale';
        $mode = 'mode';
        $allowDuplicates = true;
        $localeMock = $this->getMock('Magento\Tools\I18n\Code\Locale', array(), array(), '', false);

        $this->_factoryMock->expects($this->once())->method('createLocale')->with($localeString)
            ->will($this->returnValue($localeMock));
        $this->_dictionaryLoaderMock->expects($this->once())->method('load')->with($dictionaryPath)
            ->will($this->returnValue($this->_dictionaryMock));
        $this->_packWriterMock->expects($this->once())->method('write')
            ->with($this->_dictionaryMock, $packPath, $localeMock, $mode);

        $this->_generator->generate($dictionaryPath, $packPath, $localeString, $mode, $allowDuplicates);
    }

    public function testGenerateWithNotAllowedDuplicatesAndDuplicatesExist()
    {
        $error = "Error. The phrase \"phrase1\" is translated differently in 1 places.\n"
            . "Error. The phrase \"phrase2\" is translated differently in 1 places.\n";
        $this->setExpectedException('\RuntimeException', $error);

        $allowDuplicates = false;

        $phraseFirstMock = $this->getMock('Magento\Tools\I18n\Code\Dictionary\Phrase', array(), array(), '', false);
        $phraseFirstMock->expects($this->once())->method('getPhrase')->will($this->returnValue('phrase1'));
        $phraseSecondMock = $this->getMock('Magento\Tools\I18n\Code\Dictionary\Phrase', array(), array(), '', false);
        $phraseSecondMock->expects($this->once())->method('getPhrase')->will($this->returnValue('phrase2'));

        $this->_dictionaryLoaderMock->expects($this->any())->method('load')
            ->will($this->returnValue($this->_dictionaryMock));
        $this->_dictionaryMock->expects($this->once())->method('getDuplicates')->will($this->returnValue(array(
            array($phraseFirstMock), array($phraseSecondMock))));

        $this->_generator->generate('dictionary_path', 'pack_path', 'locale', 'mode', $allowDuplicates);
    }
}
