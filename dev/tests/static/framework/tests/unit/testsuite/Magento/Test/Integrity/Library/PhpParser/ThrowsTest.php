<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Library\PhpParser;

use Magento\TestFramework\Integrity\Library\PhpParser\Throws;

/**
 */
class ThrowsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Throws
     */
    protected $throws;

    /**
     * @var \Magento\TestFramework\Integrity\Library\PhpParser\Tokens|\PHPUnit_Framework_MockObject_MockObject
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
     * Test get throws dependencies
     *
     * @test
     */
    public function testGetDependencies()
    {
        $tokens = [
            0 => [T_THROW, 'throw'],
            1 => [T_WHITESPACE, ' '],
            2 => [T_NEW, 'new'],
            3 => [T_WHITESPACE, ' '],
            4 => [T_NS_SEPARATOR, '\\'],
            5 => [T_STRING, 'Exception'],
            6 => '(',
        ];

        $this->tokens->expects($this->any())->method('getTokenCodeByKey')->will(
            $this->returnCallback(
                function ($k) use ($tokens) {
                    return $tokens[$k][0];
                }
            )
        );

        $this->tokens->expects($this->any())->method('getTokenValueByKey')->will(
            $this->returnCallback(
                function ($k) use ($tokens) {
                    return $tokens[$k][1];
                }
            )
        );

        $throws = new Throws($this->tokens);
        foreach ($tokens as $k => $token) {
            $throws->parse($token, $k);
        }

        $uses = $this->getMockBuilder(
            \Magento\TestFramework\Integrity\Library\PhpParser\Uses::class
        )->disableOriginalConstructor()->getMock();

        $uses->expects($this->once())->method('hasUses')->will($this->returnValue(true));

        $uses->expects($this->once())->method('getClassNameWithNamespace')->will($this->returnValue('\Exception'));

        $this->assertEquals(['\Exception'], $throws->getDependencies($uses));
    }
}
