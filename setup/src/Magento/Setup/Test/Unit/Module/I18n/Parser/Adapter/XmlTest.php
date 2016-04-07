<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n\Parser\Adapter;

class XmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_testFile;

    /**
     * @var \Magento\Setup\Module\I18n\Parser\Adapter\Xml
     */
    protected $_adapter;

    protected function setUp()
    {
        $this->_testFile = str_replace('\\', '/', realpath(dirname(__FILE__))) . '/_files/default.xml';

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_adapter = $objectManagerHelper->getObject('Magento\Setup\Module\I18n\Parser\Adapter\Xml');
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
