<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\I18n\Parser\Adapter\Php;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer
 */
class TokenizerTest extends TestCase
{
    /**
     * @var Tokenizer
     */
    protected $tokenizer;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->tokenizer = $this->objectManager->getObject(
            Tokenizer::class
        );
    }

    /**
     * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer::isMatchingClass
     */
    public function testIsMatchingClass()
    {
        $class = 'Phrase';
        $this->parseFile();
        $this->assertFalse($this->tokenizer->isMatchingClass($class)); // new
        $this->assertTrue($this->tokenizer->isMatchingClass($class)); // \Magento\Framework\Phrase(
        $this->assertFalse($this->tokenizer->isMatchingClass($class)); // 'Testing'
        $this->assertFalse($this->tokenizer->isMatchingClass($class)); // )
        $this->assertFalse($this->tokenizer->isMatchingClass($class)); // ;
        $this->assertFalse($this->tokenizer->isMatchingClass($class)); // new
        $this->assertTrue($this->tokenizer->isMatchingClass($class)); // Phrase(
        $this->assertFalse($this->tokenizer->isMatchingClass($class)); // 'More testing'
        $this->assertFalse($this->tokenizer->isMatchingClass($class)); // )
        $this->assertFalse($this->tokenizer->isMatchingClass($class)); // ;
        $this->assertFalse($this->tokenizer->isMatchingClass($class)); // new
        $this->assertFalse($this->tokenizer->isMatchingClass($class)); // \Magento\Framework\DataObject(
        $this->assertFalse($this->tokenizer->isMatchingClass($class)); // )
        $this->assertFalse($this->tokenizer->isMatchingClass($class)); // ;
    }

    /**
     * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer::getNextRealToken
     */
    public function testGetNextRealToken()
    {
        $this->parseFile();
        $this->assertEquals('new', $this->tokenizer->getNextRealToken()->getValue());
        $this->assertEquals('\\', $this->tokenizer->getNextRealToken()->getValue());
        $this->assertEquals('Magento', $this->tokenizer->getNextRealToken()->getValue());
        $this->assertEquals('\\', $this->tokenizer->getNextRealToken()->getValue());
        $this->assertEquals('Framework', $this->tokenizer->getNextRealToken()->getValue());
        $this->assertEquals('\\', $this->tokenizer->getNextRealToken()->getValue());
        $this->assertEquals('Phrase', $this->tokenizer->getNextRealToken()->getValue());
        $this->assertEquals('(', $this->tokenizer->getNextRealToken()->getValue());
        $this->assertEquals('\'Testing\'', $this->tokenizer->getNextRealToken()->getValue());
        $this->assertEquals(')', $this->tokenizer->getNextRealToken()->getValue());
        $this->assertEquals(';', $this->tokenizer->getNextRealToken()->getValue());
    }

    /**
     * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer::isEndOfLoop
     */
    public function testIsEndOfLoop()
    {
        $this->parseFile();
        //We have 27 total tokens in objectsCode.php file (excluding whitespaces)
        //So the isEndOfLoop function should return true after we pick 28th non-existent token
        for ($i = 0; $i < 28; $i++) {
            $this->assertFalse($this->tokenizer->isEndOfLoop());
            $this->tokenizer->getNextRealToken();
        }
        $this->assertTrue($this->tokenizer->isEndOfLoop());
    }

    protected function parseFile()
    {
        $file = __DIR__ . '/_files/objectsCode.php.txt';
        $this->tokenizer->parse($file);
    }
}
