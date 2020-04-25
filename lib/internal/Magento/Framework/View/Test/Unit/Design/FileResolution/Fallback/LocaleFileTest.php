<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Design\FileResolution\Fallback;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface;
use Magento\Framework\View\Design\ThemeInterface;
use \Magento\Framework\View\Design\FileResolution\Fallback\LocaleFile;

use Magento\Framework\View\Design\Fallback\RulePool;

class LocaleFileTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $resolver;

    /**
     * @var LocaleFile
     */
    protected $object;

    protected function setUp(): void
    {
        $this->resolver = $this->createMock(
            ResolverInterface::class
        );
        $this->object = new LocaleFile($this->resolver);
    }

    public function testGetFile()
    {
        $theme = $this->getMockForAbstractClass(ThemeInterface::class);
        $expected = 'some/file.ext';
        $this->resolver->expects($this->once())
            ->method('resolve')
            ->with(RulePool::TYPE_LOCALE_FILE, 'file.ext', 'frontend', $theme, 'en_US', null)
            ->will($this->returnValue($expected));
        $actual = $this->object->getFile('frontend', $theme, 'en_US', 'file.ext');
        $this->assertSame($expected, $actual);
    }
}
