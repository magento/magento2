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
namespace Magento\Test\Tools\I18n\Code\Dictionary\Writer;

class CsvTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_testFile;

    /**
     * @var \Magento\Tools\I18n\Code\Dictionary\Phrase|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_phraseFirstMock;

    /**
     * @var \Magento\Tools\I18n\Code\Dictionary\Phrase|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_phraseSecondMock;

    protected function setUp()
    {
        $this->_testFile = str_replace('\\', '/', realpath(dirname(__FILE__))) . '/_files/test.csv';

        $this->_phraseFirstMock = $this->getMock(
            'Magento\Tools\I18n\Code\Dictionary\Phrase',
            array(),
            array(),
            '',
            false
        );
        $this->_phraseSecondMock = $this->getMock(
            'Magento\Tools\I18n\Code\Dictionary\Phrase',
            array(),
            array(),
            '',
            false
        );
    }

    protected function tearDown()
    {
        if (file_exists($this->_testFile)) {
            unlink($this->_testFile);
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Cannot open file for write dictionary: "wrong/path"
     */
    public function testWrongOutputFile()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $objectManagerHelper->getObject(
            'Magento\Tools\I18n\Code\Dictionary\Writer\Csv',
            array('outputFilename' => 'wrong/path')
        );
    }

    public function testWrite()
    {
        $this->_phraseFirstMock->expects(
            $this->once()
        )->method(
            'getPhrase'
        )->will(
            $this->returnValue("phrase1_quote\\'")
        );
        $this->_phraseFirstMock->expects(
            $this->once()
        )->method(
            'getTranslation'
        )->will(
            $this->returnValue("translation1_quote\\'")
        );
        $this->_phraseFirstMock->expects(
            $this->once()
        )->method(
            'getContextType'
        )->will(
            $this->returnValue("context_type1_quote\\'")
        );
        $this->_phraseFirstMock->expects(
            $this->once()
        )->method(
            'getContextValueAsString'
        )->will(
            $this->returnValue("content_value1_quote\\'")
        );

        $this->_phraseSecondMock->expects(
            $this->once()
        )->method(
            'getPhrase'
        )->will(
            $this->returnValue("phrase2_quote\\'")
        );
        $this->_phraseSecondMock->expects(
            $this->once()
        )->method(
            'getTranslation'
        )->will(
            $this->returnValue("translation2_quote\\'")
        );
        $this->_phraseSecondMock->expects(
            $this->once()
        )->method(
            'getContextType'
        )->will(
            $this->returnValue("context_type2_quote\\'")
        );
        $this->_phraseSecondMock->expects(
            $this->once()
        )->method(
            'getContextValueAsString'
        )->will(
            $this->returnValue("content_value2_quote\\'")
        );

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var \Magento\Tools\I18n\Code\Dictionary\Writer\Csv $writer */
        $writer = $objectManagerHelper->getObject(
            'Magento\Tools\I18n\Code\Dictionary\Writer\Csv',
            array('outputFilename' => $this->_testFile)
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
        $this->_phraseFirstMock->expects($this->once())->method('getPhrase')->will($this->returnValue('phrase1'));
        $this->_phraseFirstMock->expects(
            $this->once()
        )->method(
            'getTranslation'
        )->will(
            $this->returnValue('translation1')
        );
        $this->_phraseFirstMock->expects($this->once())->method('getContextType')->will($this->returnValue(''));

        $this->_phraseSecondMock->expects($this->once())->method('getPhrase')->will($this->returnValue('phrase2'));
        $this->_phraseSecondMock->expects(
            $this->once()
        )->method(
            'getTranslation'
        )->will(
            $this->returnValue('translation2')
        );
        $this->_phraseSecondMock->expects(
            $this->once()
        )->method(
            'getContextType'
        )->will(
            $this->returnValue('context_type2')
        );
        $this->_phraseSecondMock->expects(
            $this->once()
        )->method(
            'getContextValueAsString'
        )->will(
            $this->returnValue('')
        );

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var \Magento\Tools\I18n\Code\Dictionary\Writer\Csv $writer */
        $writer = $objectManagerHelper->getObject(
            'Magento\Tools\I18n\Code\Dictionary\Writer\Csv',
            array('outputFilename' => $this->_testFile)
        );
        $writer->write($this->_phraseFirstMock);
        $writer->write($this->_phraseSecondMock);

        $expected = "phrase1,translation1\nphrase2,translation2\n";
        $this->assertEquals($expected, file_get_contents($this->_testFile));
    }
}
