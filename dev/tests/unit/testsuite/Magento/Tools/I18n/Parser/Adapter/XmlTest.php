<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\I18n\Parser\Adapter;

class XmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_testFile;

    /**
     * @var \Magento\Tools\I18n\Parser\Adapter\Xml
     */
    protected $_adapter;

    protected function setUp()
    {
        // dev/tests/unit/testsuite/tools/I18n/Parser/Adapter/_files/layout.xml
        $this->_testFile = str_replace('\\', '/', realpath(dirname(__FILE__))) . '/_files/default.xml';

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_adapter = $objectManagerHelper->getObject('Magento\Tools\I18n\Parser\Adapter\Xml');
    }

    public function testParse()
    {
        $expectedResult = [
            ['phrase' => 'Phrase 2', 'file' => $this->_testFile, 'line' => '', 'quote' => ''],
            ['phrase' => 'Phrase 3', 'file' => $this->_testFile, 'line' => '', 'quote' => ''],
            ['phrase' => 'Phrase 1', 'file' => $this->_testFile, 'line' => '', 'quote' => ''],
        ];

        $this->_adapter->parse($this->_testFile);

        $this->assertEquals($expectedResult, $this->_adapter->getPhrases());
    }
}
