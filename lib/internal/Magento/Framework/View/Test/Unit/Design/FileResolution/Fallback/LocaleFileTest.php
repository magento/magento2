<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Design\FileResolution\Fallback;

use \Magento\Framework\View\Design\FileResolution\Fallback\LocaleFile;

use Magento\Framework\View\Design\Fallback\RulePool;

class LocaleFileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $resolver;

    /**
     * @var LocaleFile
     */
    protected $object;

    protected function setUp(): void
    {
        $this->resolver = $this->createMock(
            \Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface::class
        );
        $this->object = new LocaleFile($this->resolver);
    }

    public function testGetFile()
    {
        $theme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
        $expected = 'some/file.ext';
        $this->resolver->expects($this->once())
            ->method('resolve')
            ->with(RulePool::TYPE_LOCALE_FILE, 'file.ext', 'frontend', $theme, 'en_US', null)
            ->willReturn($expected);
        $actual = $this->object->getFile('frontend', $theme, 'en_US', 'file.ext');
        $this->assertSame($expected, $actual);
    }
}
