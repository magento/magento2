<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Library\PhpParser;

use Magento\TestFramework\Integrity\Library\PhpParser\Tokens;

/**
 */
class TokensTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Tokens
     */
    protected $tokens;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\TestFramework\Integrity\Library\PhpParser\ParserFactory
     */
    protected $parseFactory;

    /**
     * Testable content
     *
     * @var string
     */
    protected $content = '<?php echo "test";';

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->parseFactory = $this->getMockBuilder(
            \Magento\TestFramework\Integrity\Library\PhpParser\ParserFactory::class
        )->disableOriginalConstructor()->getMock();
    }

    /**
     * Covered parse content
     *
     * @test
     */
    public function testParseContent()
    {
        $parser = $this->createMock(\Magento\TestFramework\Integrity\Library\PhpParser\ParserInterface::class);

        $this->parseFactory->expects($this->any())->method('createParsers')->willReturn([$parser]);

        $this->tokens = new Tokens($this->content, $this->parseFactory);
        $this->tokens->parseContent();
    }

    /**
     * Covered getDependencies method
     *
     * @test
     */
    public function testGetDependencies()
    {
        $uses = $this->getMockBuilder(
            \Magento\TestFramework\Integrity\Library\PhpParser\Uses::class
        )->disableOriginalConstructor()->getMock();

        $this->parseFactory->expects($this->exactly(2))->method('getUses')->willReturn($uses);

        $staticCalls = $this->getMockBuilder(
            \Magento\TestFramework\Integrity\Library\PhpParser\StaticCalls::class
        )->disableOriginalConstructor()->getMock();

        $staticCalls->expects(
            $this->once()
        )->method(
            'getDependencies'
        )->willReturn(
            ['StaticDependency']
        );

        $this->parseFactory->expects($this->once())->method('getStaticCalls')->willReturn($staticCalls);

        $throws = $this->getMockBuilder(
            \Magento\TestFramework\Integrity\Library\PhpParser\Throws::class
        )->disableOriginalConstructor()->getMock();

        $throws->expects($this->once())->method('getDependencies')->willReturn(['ThrowDependency']);

        $this->parseFactory->expects($this->once())->method('getThrows')->willReturn($throws);

        $this->tokens = new Tokens($this->content, $this->parseFactory);
        $this->assertEquals(['StaticDependency', 'ThrowDependency'], $this->tokens->getDependencies());
    }

    /**
     * Test code for get previous token from parameter "content"
     *
     * @test
     */
    public function testGetPreviousToken()
    {
        $this->tokens = new Tokens($this->content, $this->parseFactory);
        $this->assertEquals([T_ECHO, 'echo', 1], $this->tokens->getPreviousToken(2));
    }

    /**
     * Covered getTokenCodeByKey
     *
     * @test
     */
    public function testGetTokenCodeByKey()
    {
        $this->tokens = new Tokens($this->content, $this->parseFactory);
        $this->assertEquals(T_ECHO, $this->tokens->getTokenCodeByKey(1));
    }

    /**
     * Covered getTokenValueByKey
     *
     * @test
     */
    public function testGetTokenValueByKey()
    {
        $this->tokens = new Tokens($this->content, $this->parseFactory);
        $this->assertEquals('echo', $this->tokens->getTokenValueByKey(1));
    }
}
