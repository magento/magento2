<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Design\Config;

use Magento\Theme\Model\Design\Config\ValueChecker;

class ValueCheckerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\App\ScopeFallbackResolverInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $fallbackResolver;

    /** @var \Magento\Framework\App\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $appConfig;

    /** @var ValueChecker */
    protected $valueChecker;

    /** @var \Magento\Theme\Model\Design\Config\ValueProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $valueProcessor;

    public function setUp()
    {
        $this->fallbackResolver = $this->getMockForAbstractClass(
            'Magento\Framework\App\ScopeFallbackResolverInterface',
            [],
            '',
            false
        );
        $this->appConfig = $this->getMock('Magento\Framework\App\Config', [], [], '', false);
        $this->valueProcessor = $this->getMockBuilder('Magento\Theme\Model\Design\Config\ValueProcessor')
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
