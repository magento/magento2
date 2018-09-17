<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Asset\PreProcessor;

use Magento\Framework\View\Asset\Minification;
use Magento\Framework\View\Asset\PreProcessor\MinificationFilenameResolver;

/**
 * Class MinificationFilenameResolverTest
 *
 * @see \Magento\Framework\View\Asset\PreProcessor\MinificationFilenameResolver
 */
class MinificationFilenameResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Run test for resolve method
     *
     * @param bool $isMin
     * @param string $input
     * @param string $expected
     *
     * @dataProvider dataProviderForTestResolve
     */
    public function testResolve($isMin, $input, $expected)
    {
        $minificationMock = $this->getMockBuilder(Minification::class)
            ->disableOriginalConstructor()
            ->getMock();

        $minificationMock->expects(self::once())
            ->method('isEnabled')
            ->with('ext')
            ->willReturn($isMin);

        $resolver = new MinificationFilenameResolver($minificationMock);

        self::assertEquals($expected, $resolver->resolve($input));
    }

    /**
     * @return array
     */
    public function dataProviderForTestResolve()
    {
        return [
            [
                'isMin' => true,
                'input' => 'test.min.ext',
                'expected' => 'test.ext'
            ],
            [
                'isMin' => false,
                'input' => 'test.min.ext',
                'expected' => 'test.min.ext'
            ],
            [
                'isMin' => false,
                'input' => 'test.ext',
                'expected' => 'test.ext'
            ]
        ];
    }
}
