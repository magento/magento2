<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Asset\PreProcessor;

use Magento\Framework\View\Asset\Minification;
use Magento\Framework\View\Asset\PreProcessor\MinificationConfigProvider;
use Magento\Framework\View\Asset\PreProcessor\MinificationFilenameResolver;
use PHPUnit\Framework\TestCase;

/**
 *
 * @see \Magento\Framework\View\Asset\PreProcessor\MinificationFilenameResolver
 */
class MinificationFilenameResolverTest extends TestCase
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
        $minificationConfigMock = $this->getMockBuilder(MinificationConfigProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $minificationConfigMock->expects(self::once())
            ->method('isMinificationEnabled')
            ->with($input)
            ->willReturn($isMin);

        $resolver = new MinificationFilenameResolver($minificationMock, $minificationConfigMock);

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
