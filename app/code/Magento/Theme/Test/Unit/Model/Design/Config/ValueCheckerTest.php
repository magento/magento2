<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Design\Config;

use Magento\Framework\App\Config;
use Magento\Framework\App\ScopeFallbackResolverInterface;
use Magento\Theme\Model\Design\Config\ValueChecker;
use Magento\Theme\Model\Design\Config\ValueProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValueCheckerTest extends TestCase
{
    /** @var ScopeFallbackResolverInterface|MockObject */
    protected $fallbackResolver;

    /** @var Config|MockObject */
    protected $appConfig;

    /** @var ValueChecker */
    protected $valueChecker;

    /** @var ValueProcessor|MockObject */
    protected $valueProcessor;

    protected function setUp(): void
    {
        $this->fallbackResolver = $this->getMockForAbstractClass(
            ScopeFallbackResolverInterface::class,
            [],
            '',
            false
        );
        $this->appConfig = $this->createMock(Config::class);
        $this->valueProcessor = $this->getMockBuilder(ValueProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->valueChecker  = new ValueChecker(
            $this->fallbackResolver,
            $this->appConfig,
            $this->valueProcessor
        );
    }

    public function testIsDifferentFromDefault()
    {
        $this->fallbackResolver->expects($this->once())
            ->method('getFallbackScope')
            ->with('default', 0)
            ->willReturn([null, null]);

        $this->assertTrue(
            $this->valueChecker->isDifferentFromDefault(
                'value',
                'default',
                0,
                ['path' => 'design/head/default_title']
            )
        );
    }

    public function testIsDifferentFromDefaultWithWebsiteScope()
    {
        $this->fallbackResolver->expects($this->once())
            ->method('getFallbackScope')
            ->with('website', 1)
            ->willReturn(['default', 0]);
        $this->appConfig->expects($this->once())
            ->method('getValue')
            ->with('design/head/default_title', 'default', 0)
            ->willReturn('');
        $this->valueProcessor->expects($this->atLeastOnce())
            ->method('process')
            ->willReturnArgument(0);

        $this->assertTrue(
            $this->valueChecker->isDifferentFromDefault(
                'value',
                'website',
                1,
                ['path' => 'design/head/default_title']
            )
        );
    }

    public function testIsDifferentFromDefaultWithArrays()
    {
        $path = 'design/head/default_title';
        $this->fallbackResolver->expects($this->once())
            ->method('getFallbackScope')
            ->with('website', 1)
            ->willReturn(['default', 0]);
        $this->appConfig
            ->expects($this->once())
            ->method('getValue')
            ->with($path, 'default', 0)
            ->willReturn([
                [
                    'qwe' => 123
                ],
            ]);
        $this->valueProcessor->expects($this->atLeastOnce())
            ->method('process')
            ->willReturnArgument(0);
        $this->assertTrue(
            $this->valueChecker->isDifferentFromDefault(
                [
                    [
                        'sdf' => 1
                    ],

                ],
                'website',
                1,
                ['path' => $path]
            )
        );
    }
}
