<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n\Parser\Adapter;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\I18n\Dictionary\Phrase;

class JsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_testFile;

    /**
     * @var int
     */
    protected $_stringsCount;

    /**
     * @var \Magento\Setup\Module\I18n\Parser\Adapter\Js
     */
    protected $_adapter;

    protected function setUp()
    {
        $this->_testFile = str_replace('\\', '/', realpath(dirname(__FILE__))) . '/_files/file.js';
        $this->_stringsCount = count(file($this->_testFile));

        $this->_adapter = (new ObjectManager($this))->getObject('Magento\Setup\Module\I18n\Parser\Adapter\Js');
    }

    public function testParse()
    {
        $expectedResult = [
            [
                'phrase' => 'Phrase 1',
                'file' => $this->_testFile,
                'line' => $this->_stringsCount - 2,
                'quote' => Phrase::QUOTE_SINGLE,
            ],
            [
                'phrase' => 'Phrase 2 %1',
                'file' => $this->_testFile,
                'line' => $this->_stringsCount - 1,
                'quote' => Phrase::QUOTE_DOUBLE
            ],
        ];

        $this->_adapter->parse($this->_testFile);

        $this->assertEquals($expectedResult, $this->_adapter->getPhrases());
    }
}
