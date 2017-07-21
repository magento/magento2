<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Library\PhpParser;

use Magento\TestFramework\Integrity\Library\PhpParser\ParserFactory;

/**
 */
class ParserFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Integrity\Library\PhpParser\Tokens
     */
    protected $tokens;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->tokens = $this->getMockBuilder(
            \Magento\TestFramework\Integrity\Library\PhpParser\Tokens::class
        )->disableOriginalConstructor()->getMock();
    }

    /**
     * Covered createParsers method
     *
     * @test
     */
    public function testCreateParsers()
    {
        $parseFactory = new ParserFactory();
        $parseFactory->createParsers($this->tokens);
        $this->assertInstanceOf(
            \Magento\TestFramework\Integrity\Library\PhpParser\Uses::class,
            $parseFactory->getUses()
        );
        $this->assertInstanceOf(
            \Magento\TestFramework\Integrity\Library\PhpParser\StaticCalls::class,
            $parseFactory->getStaticCalls()
        );
        $this->assertInstanceOf(
            \Magento\TestFramework\Integrity\Library\PhpParser\Throws::class,
            $parseFactory->getThrows()
        );
    }
}
