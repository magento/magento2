<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\I18n\Parser\Adapter;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\I18n\Parser\Adapter\Xml;
use PHPUnit\Framework\TestCase;

class XmlTest extends TestCase
{
    /**
     * @var string
     */
    protected $_testFile;

    /**
     * @var Xml
     */
    protected $_adapter;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->_adapter = $objectManagerHelper->getObject(Xml::class);
    }

    /**
     * @dataProvider parseDataProvider
     * @param string $file
     * @param array $expectedResult
     * @return void
     */
    public function testParse(string $file, array $expectedResult)
    {
        $this->_adapter->parse($file);

        $this->assertEquals($expectedResult, $this->_adapter->getPhrases());
    }

    /**
     * Provide files and parse results for testParse.
     *
     * @return array
     */
    public function parseDataProvider()
    {
        $default = str_replace('\\', '/', realpath(dirname(__FILE__))) . '/_files/default.xml';
        $defaultDi = str_replace('\\', '/', realpath(dirname(__FILE__))) . '/_files/default_di.xml';

        return [
            [
                'file' => $default,
                'expectedResult' => [
                    ['phrase' => 'Phrase 2', 'file' => $default, 'line' => '', 'quote' => ''],
                    ['phrase' => 'Phrase 3', 'file' => $default, 'line' => '', 'quote' => ''],
                    ['phrase' => 'Phrase 1', 'file' => $default, 'line' => '', 'quote' => ''],
                    ['phrase' => 'Comment from new line.', 'file' => $default, 'line' => '', 'quote' => ''],
                ],
            ],
            [
                'file' => $defaultDi,
                'expectedResult' => [
                    ['phrase' => 'Phrase 1', 'file' => $defaultDi, 'line' => '', 'quote' => ''],
                ],
            ],
        ];
    }
}
