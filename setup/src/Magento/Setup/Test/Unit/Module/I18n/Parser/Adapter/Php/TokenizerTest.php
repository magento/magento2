<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\I18n\Parser\Adapter\Php;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer;
use PHPUnit\Framework\Attributes\RequiresPhp;
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
     * Test getting next Real token for PHP > 8, where namespaced names are treated as single token.
     *
     * @return void
     */
    #[RequiresPhp('>=8.0')]
    public function testGetNextRealTokenWhenNamespaceIsSingleToken(): void
    {
        $this->parseFile();
        $this->assertEquals('new', $this->tokenizer->getNextRealToken()->getValue());
        $this->assertEquals('\\Magento\\Framework\\Phrase', $this->tokenizer->getNextRealToken()->getValue());
        $this->assertEquals('(', $this->tokenizer->getNextRealToken()->getValue());
        $this->assertEquals('\'Testing\'', $this->tokenizer->getNextRealToken()->getValue());
        $this->assertEquals(')', $this->tokenizer->getNextRealToken()->getValue());
        $this->assertEquals(';', $this->tokenizer->getNextRealToken()->getValue());
    }

    /**
     * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer::getNextRealToken
     * @return void
     */
    #[RequiresPhp('<8.0')]
    public function testGetNextRealToken(): void
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
     * @return void
     */
    #[RequiresPhp('<8.0')]
    public function testIsEndOfLoop(): void
    {
        $this->parseFile();
        
        // PHP < 8.0: 27 total tokens in objectsCode.php file (excluding whitespaces)
        for ($i = 0; $i < 27; $i++) {
            $this->assertFalse($this->tokenizer->isEndOfLoop());
            $this->tokenizer->getNextRealToken();
        }
        
        $this->assertTrue($this->tokenizer->isEndOfLoop());
    }

    /**
     * Test isEndOfLoop for PHP >= 8.0, where namespaces are single tokens.
     *
     * In PHP >= 8.0, we have 18 tokens because namespaces like \Magento\Framework\Phrase
     * are treated as single T_NAME_FULLY_QUALIFIED tokens.
     *
     * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer::isEndOfLoop
     * @return void
     */
    #[RequiresPhp('>=8.0')]
    public function testIsEndOfLoopWhenNamespaceIsSingleToken(): void
    {
        $this->parseFile();
        
        // PHP >= 8.0: 18 tokens (namespaces as single T_NAME_FULLY_QUALIFIED tokens)
        for ($i = 0; $i < 18; $i++) {
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
