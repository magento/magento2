<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n\Parser\Adapter;

class XmlTest extends \PHPUnit\Framework\TestCase
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
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_adapter = $objectManagerHelper->getObject(\Magento\Setup\Module\I18n\Parser\Adapter\Xml::class);
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
