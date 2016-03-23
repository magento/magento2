<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n\Parser\Adapter\Php;

use \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer
 */
class TokenizerTest extends \PHPUnit_Framework_TestCase
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
            'Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer'
        );
    }

    /**
     * @covers \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer::isMatchingClass
     */
    public function testIsMatchingClass()
    {
        $class = 'Phrase';
        $this->parseFile();
        $this->assertEquals(false, $this->tokenizer->isMatchingClass($class)); // new
        $this->assertEquals(true, $this->tokenizer->isMatchingClass($class)); // \Magento\Framework\Phrase(
        $this->assertEquals(false, $this->tokenizer->isMatchingClass($class)); // 'Testing'
        $this->assertEquals(false, $this->tokenizer->isMatchingClass($class)); // )
        $this->assertEquals(false, $this->tokenizer->isMatchingClass($class)); // ;
        $this->assertEquals(false, $this->tokenizer->isMatchingClass($class)); // new
        $this->assertEquals(true, $this->tokenizer->isMatchingClass($class)); // Phrase(
        $this->assertEquals(false, $this->tokenizer->isMatchingClass($class)); // 'More testing'
        $this->assertEquals(false, $this->tokenizer->isMatchingClass($class)); // )
        $this->assertEquals(false, $this->tokenizer->isMatchingClass($class)); // ;
        $this->assertEquals(false, $this->tokenizer->isMatchingClass($class)); // new
        $this->assertEquals(false, $this->tokenizer->isMatchingClass($class)); // \Magento\Framework\DataObject(
        $this->assertEquals(false, $this->tokenizer->isMatchingClass($class)); // )
        $this->assertEquals(false, $this->tokenizer->isMatchingClass($class)); // ;
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
