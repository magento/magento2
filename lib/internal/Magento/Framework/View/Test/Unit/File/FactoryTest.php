<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\File;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File;
use Magento\Framework\View\File\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @var Factory
     */
    private $_model;

    /**
     * @var MockObject
     */
    private $_objectManager;

    protected function setUp(): void
    {
        $this->_objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->_model = new Factory($this->_objectManager);
    }

    public function testCreate()
    {
        $theme = $this->getMockForAbstractClass(ThemeInterface::class);
        $file = new File(__FILE__, 'Fixture_Module', $theme);
        $isBase = true;
        $this->_objectManager
            ->expects($this->once())
            ->method('create')
            ->with(
                File::class,
                $this->identicalTo([
                    'filename' => __FILE__,
                    'module' => 'Fixture_Module',
                    'theme' => $theme,
                    'isBase' => $isBase,
                ])
            )
            ->willReturn($file);
        $this->assertSame($file, $this->_model->create(__FILE__, 'Fixture_Module', $theme, $isBase));
    }
}
