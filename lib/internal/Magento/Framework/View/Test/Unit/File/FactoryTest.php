<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\File;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\File\Factory
     */
    private $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $_objectManager;

    protected function setUp(): void
    {
        $this->_objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_model = new \Magento\Framework\View\File\Factory($this->_objectManager);
    }

    public function testCreate()
    {
        $theme = $this->getMockForAbstractClass(\Magento\Framework\View\Design\ThemeInterface::class);
        $file = new \Magento\Framework\View\File(__FILE__, 'Fixture_Module', $theme);
        $isBase = true;
        $this->_objectManager
            ->expects($this->once())
            ->method('create')
            ->with(
                \Magento\Framework\View\File::class,
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
