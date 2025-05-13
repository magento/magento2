<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\I18n\Parser\Adapter;

use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\I18n\Dictionary\Phrase;
use Magento\Setup\Module\I18n\Parser\Adapter\Js;
use PHPUnit\Framework\TestCase;

class JsTest extends TestCase
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
     * @var Js
     */
    protected $_adapter;

    protected function setUp(): void
    {
        $this->_testFile = str_replace('\\', '/', realpath(dirname(__FILE__))) . '/_files/file.js';
        $this->_stringsCount = count(file($this->_testFile));
        $filesystem = new File();
        $this->_adapter = (new ObjectManager($this))->getObject(Js::class, ['filesystem' => $filesystem]);
    }

    public function testParse()
    {
        $expectedResult = [
            [
                'phrase' => 'Phrase 1',
                'file' => $this->_testFile,
                'line' => 1,
                'quote' => Phrase::QUOTE_SINGLE,
            ],
            [
                'phrase' => 'Phrase 2 %1',
                'file' => $this->_testFile,
                'line' => 1,
                'quote' => Phrase::QUOTE_DOUBLE
            ],
            [
                'phrase' => 'Field ',
                'file' => $this->_testFile,
                'line' => 1,
                'quote' => Phrase::QUOTE_SINGLE
            ],
            [
                'phrase' => ' is required.',
                'file' => $this->_testFile,
                'line' => 1,
                'quote' => Phrase::QUOTE_SINGLE
            ],
            [
                'phrase' => 'Welcome, %1!',
                'file' => $this->_testFile,
                'line' => 1,
                'quote' => Phrase::QUOTE_SINGLE
            ]
        ];

        $this->_adapter->parse($this->_testFile);

        $this->assertEquals($expectedResult, $this->_adapter->getPhrases());
    }
}
