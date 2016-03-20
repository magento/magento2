<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Library\PhpParser;

use Magento\TestFramework\Integrity\Library\PhpParser\StaticCalls;

/**
 */
class StaticCallsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StaticCalls
     */
    protected $staticCalls;

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
            'Magento\TestFramework\Integrity\Library\PhpParser\Tokens'
        )->disableOriginalConstructor()->getMock();
    }

    /**
     * Test get static call dependencies
     *
     * @test
     */
    public function testGetDependencies()
    {
        $tokens = [
            0 => [T_WHITESPACE, ' '],
            1 => [T_NS_SEPARATOR, '\\'],
            2 => [T_STRING, 'Object'],
            3 => [T_PAAMAYIM_NEKUDOTAYIM, '::'],
        ];

        $this->tokens->expects($this->any())->method('getPreviousToken')->will(
            $this->returnCallback(
                function ($k) use ($tokens) {
                    return $tokens[$k - 1];
                }
            )
        );

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

        $throws = new StaticCalls($this->tokens);
        foreach ($tokens as $k => $token) {
            $throws->parse($token, $k);
        }

        $uses = $this->getMockBuilder(
            'Magento\TestFramework\Integrity\Library\PhpParser\Uses'
        )->disableOriginalConstructor()->getMock();

        $uses->expects($this->once())->method('hasUses')->will($this->returnValue(true));

        $uses->expects($this->once())->method('getClassNameWithNamespace')->will($this->returnValue('\Object'));

        $this->assertEquals(['\Object'], $throws->getDependencies($uses));
    }
}
