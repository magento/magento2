<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector
 */
class PhraseCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PhraseCollector
     */
    protected $phraseCollector;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->phraseCollector = $this->objectManager->create(
            \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector::class
        );
    }

    /**
     * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector::parse
     */
    public function testParse()
    {
        $file = __DIR__.'/_files/objectsCode.php.txt';
        $this->phraseCollector->setIncludeObjects();
        $this->phraseCollector->parse($file);
        $expectation = [
            [
                'phrase' => '\'Testing\'',
                'arguments' => 0,
                'file' => $file,
                'line' => 3
            ],
            [
                'phrase' => '\'More testing\'',
                'arguments' => 0,
                'file' => $file,
                'line' => 4
            ]
        ];
        $this->assertEquals($expectation, $this->phraseCollector->getPhrases());
    }
}
