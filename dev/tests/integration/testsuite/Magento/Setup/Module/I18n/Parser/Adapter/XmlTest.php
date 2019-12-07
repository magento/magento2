<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Parser\Adapter;

/**
 * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Xml
 *
 */
class XmlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Xml
     */
    protected $xmlPhraseCollector;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->xmlPhraseCollector = $objectManager->create(
            \Magento\Setup\Module\I18n\Parser\Adapter\Xml::class
        );
    }

    public function testParse()
    {
        $file = __DIR__ . '/_files/xmlPhrasesForTest.xml';
        $this->xmlPhraseCollector->parse($file);
        $expectation = [
            [
                'phrase' => 'Name only',
                'file' => $file,
                'line' => '',
                'quote' => ''
            ],
            [
                'phrase' => 'Name and title space delimiter',
                'file' => $file,
                'line' => '',
                'quote' => ''
            ],
            [
                'phrase' => 'title1',
                'file' => $file,
                'line' => '',
                'quote' => ''
            ],
            [
                'phrase' => 'title2',
                'file' => $file,
                'line' => '',
                'quote' => ''
            ],
            [
                'phrase' => 'Name only in sub node',
                'file' => $file,
                'line' => '',
                'quote' => ''
            ],
            [
                'phrase' => 'Text outside of attribute',
                'file' => $file,
                'line' => '',
                'quote' => ''
            ]
        ];
        $this->assertEquals($expectation, $this->xmlPhraseCollector->getPhrases());
    }
}
