<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Integrity\Library\PhpParser;

use PHPUnit\Framework\TestCase;

/**
 * Check static calls parsing
 */
class StaticCallsTest extends TestCase
{
    /**
     * @var StaticCalls
     */
    protected $staticCalls;

    /**
     * @var \Magento\TestFramework\Integrity\Library\PhpParser\Tokens|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $tokens;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->tokens = $this->getMockBuilder(
            \Magento\TestFramework\Integrity\Library\PhpParser\Tokens::class
        )->disableOriginalConstructor()->getMock();
    }

    /**
     * Test get static call dependencies
     *
     * @dataProvider tokensDataProvider
     */
    public function testGetDependencies(array $tokens)
    {
        $this->tokens->method('getPreviousToken')
            ->willReturnCallback(
                function ($k) use ($tokens) {
                    return $tokens[$k - 1];
                }
            );

        $this->tokens->method('getTokenCodeByKey')
            ->willReturnCallback(
                function ($k) use ($tokens) {
                    return $tokens[$k][0];
                }
            );

        $this->tokens->method('getTokenValueByKey')
            ->willReturnCallback(
                function ($k) use ($tokens) {
                    return $tokens[$k][1];
                }
            );

        $staticCalls = new StaticCalls($this->tokens);
        foreach ($tokens as $k => $token) {
            $staticCalls->parse($token, $k);
        }

        $uses = $this->getMockBuilder(
            \Magento\TestFramework\Integrity\Library\PhpParser\Uses::class
        )->disableOriginalConstructor()->getMock();

        $this->assertEquals(['\Object'], $staticCalls->getDependencies($uses));
    }

    /**
     * different tokens data for php 7 and php 8
     *
     * @return array
     */
    public function tokensDataProvider(): array
    {
        return [
            'PHP 7' => [
                [
                    0 => [T_WHITESPACE, ' '],
                    1 => [T_NS_SEPARATOR, '\\'],
                    2 => [T_STRING, 'Object'],
                    3 => [T_PAAMAYIM_NEKUDOTAYIM, '::'],
                ]
            ],
            'PHP 8' => [
                [
                    0 => [T_WHITESPACE, ' '],
                    1 => [T_NAME_FULLY_QUALIFIED, '\\Object'],
                    2 => [T_PAAMAYIM_NEKUDOTAYIM, '::'],
                ]
            ]
        ];
    }
}
