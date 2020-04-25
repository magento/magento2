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
use \Magento\Framework\View\Design\FileResolution\Fallback\StaticFile;

use Magento\Framework\View\Design\Fallback\RulePool;

class StaticFileTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $resolver;

    /**
     * @var StaticFile
     */
    protected $object;

    protected function setUp(): void
    {
        $this->resolver = $this->createMock(
            ResolverInterface::class
        );
        $this->object = new StaticFile($this->resolver);
    }

    public function testGetFile()
    {
        $theme = $this->getMockForAbstractClass(ThemeInterface::class);
        $expected = 'some/file.ext';
        $this->resolver->expects($this->once())
            ->method('resolve')
            ->with(RulePool::TYPE_STATIC_FILE, 'file.ext', 'frontend', $theme, 'en_US', 'Magento_Module')
            ->will($this->returnValue($expected));
        $actual = $this->object->getFile('frontend', $theme, 'en_US', 'file.ext', 'Magento_Module');
        $this->assertSame($expected, $actual);
    }
}
