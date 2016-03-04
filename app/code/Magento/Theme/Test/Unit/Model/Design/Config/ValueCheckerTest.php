<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
    }

    public function testIsDifferentFromDefault()
    {
        $valueChecker = new ValueChecker(
            $this->fallbackResolver,
            $this->appConfig,
            $this->valueProcessor
        );
        $this->fallbackResolver->expects($this->once())
            ->method('getFallbackScope')
            ->with('default', 0)
            ->willReturn([null, null]);

        $this->assertTrue(
            $valueChecker->isDifferentFromDefault(
                'value',
                'default',
                0,
                'design/head/default_title'
            )
        );
    }

    public function testIsDifferentFromDefaultWithWebsiteScope()
    {
        $valueChecker = new ValueChecker(
            $this->fallbackResolver,
            $this->appConfig,
            $this->valueProcessor
        );
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
            $valueChecker->isDifferentFromDefault(
                'value',
                'website',
                1,
                'design/head/default_title'
            )
        );
    }
}
