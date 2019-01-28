<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n\Parser\Adapter\Php;

use \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer
 */
class TokenizerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Tokenizer
     */
    protected $tokenizer;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->tokenizer = $this->objectManager->getObject(
            \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer::class
        );
    }

    /**
     * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer::isMatchingClass
     */
    public function testIsMatchingClass()
    {
        $class = 'Phrase';
        $this->parseFile();
        $this->assertSame(false, $this->tokenizer->isMatchingClass($class)); // new
        $this->assertSame(true, $this->tokenizer->isMatchingClass($class)); // \Magento\Framework\Phrase(
        $this->assertSame(false, $this->tokenizer->isMatchingClass($class)); // 'Testing'
        $this->assertSame(false, $this->tokenizer->isMatchingClass($class)); // )
        $this->assertSame(false, $this->tokenizer->isMatchingClass($class)); // ;
        $this->assertSame(false, $this->tokenizer->isMatchingClass($class)); // new
        $this->assertSame(true, $this->tokenizer->isMatchingClass($class)); // Phrase(
        $this->assertSame(false, $this->tokenizer->isMatchingClass($class)); // 'More testing'
        $this->assertSame(false, $this->tokenizer->isMatchingClass($class)); // )
        $this->assertSame(false, $this->tokenizer->isMatchingClass($class)); // ;
        $this->assertSame(false, $this->tokenizer->isMatchingClass($class)); // new
        $this->assertSame(false, $this->tokenizer->isMatchingClass($class)); // \Magento\Framework\DataObject(
        $this->assertSame(false, $this->tokenizer->isMatchingClass($class)); // )
        $this->assertSame(false, $this->tokenizer->isMatchingClass($class)); // ;
    }

    /**
     * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer::getNextRealToken
     */
    public function testGetNextRealToken()
    {
        $this->parseFile();
        $this->assertSame('new', $this->tokenizer->getNextRealToken()->getValue());
        $this->assertSame('\\', $this->tokenizer->getNextRealToken()->getValue());
        $this->assertSame('Magento', $this->tokenizer->getNextRealToken()->getValue());
        $this->assertSame('\\', $this->tokenizer->getNextRealToken()->getValue());
        $this->assertSame('Framework', $this->tokenizer->getNextRealToken()->getValue());
        $this->assertSame('\\', $this->tokenizer->getNextRealToken()->getValue());
        $this->assertSame('Phrase', $this->tokenizer->getNextRealToken()->getValue());
        $this->assertSame('(', $this->tokenizer->getNextRealToken()->getValue());
        $this->assertSame('\'Testing\'', $this->tokenizer->getNextRealToken()->getValue());
        $this->assertSame(')', $this->tokenizer->getNextRealToken()->getValue());
        $this->assertSame(';', $this->tokenizer->getNextRealToken()->getValue());
    }

    /**
     * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer::isEndOfLoop
     */
    public function testIsEndOfLoop()
    {
        $this->parseFile();
        //We have 27 total tokens in objectsCode.php file (excluding whitespaces)
        //So the isEndOfLoop function should return true after we pick 28th non-existent token
        for ($i = 0; $i < 28; $i += 1) {
            $this->assertFalse($this->tokenizer->isEndOfLoop());
            $this->tokenizer->getNextRealToken();
        }
        $this->assertTrue($this->tokenizer->isEndOfLoop());
    }

    protected function parseFile()
    {
        $file = __DIR__.'/_files/objectsCode.php.txt';
        $this->tokenizer->parse($file);
    }
}
