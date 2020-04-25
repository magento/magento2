<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\File;

use PHPUnit\Framework\TestCase;
use Magento\Framework\View\File\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File;

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
        $this->_objectManager = $this->createMock(ObjectManagerInterface::class);
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
            ->will($this->returnValue($file));
        $this->assertSame($file, $this->_model->create(__FILE__, 'Fixture_Module', $theme, $isBase));
    }
}
