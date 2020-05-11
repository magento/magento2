<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Block\Adminhtml\System\Design\Theme;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\AbstractTab;
use Magento\Theme\Model\Theme;
use PHPUnit\Framework\TestCase;

class TabAbstractTest extends TestCase
{
    /**
     * @var AbstractTab
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = $this->getMockForAbstractClass(
            AbstractTab::class,
            [
                $this->createMock(Context::class),
                $this->createMock(Registry::class),
                $this->createMock(FormFactory::class),
                $this->getMockForAbstractClass(ObjectManagerInterface::class),
            ],
            '',
            true,
            false,
            true,
            ['_getCurrentTheme', 'getTabLabel']
        );
    }

    protected function tearDown(): void
    {
        unset($this->_model);
    }

    public function testGetTabTitle()
    {
        $label = 'test label';
        $this->_model->expects($this->once())->method('getTabLabel')->willReturn($label);
        $this->assertEquals($label, $this->_model->getTabTitle());
    }

    /**
     * @dataProvider canShowTabDataProvider
     * @param bool $isVirtual
     * @param int $themeId
     * @param bool $result
     */
    public function testCanShowTab($isVirtual, $themeId, $result)
    {
        $themeMock = $this->createPartialMock(Theme::class, ['isVirtual', 'getId', '__wakeup']);
        $themeMock->expects($this->any())->method('isVirtual')->willReturn($isVirtual);

        $themeMock->expects($this->any())->method('getId')->willReturn($themeId);

        $this->_model->expects($this->any())->method('_getCurrentTheme')->willReturn($themeMock);

        if ($result === true) {
            $this->assertTrue($this->_model->canShowTab());
        } else {
            $this->assertFalse($this->_model->canShowTab());
        }
    }

    /**
     * @return array
     */
    public function canShowTabDataProvider()
    {
        return [[true, 1, true], [true, 0, false], [false, 1, false]];
    }

    public function testIsHidden()
    {
        $this->assertFalse($this->_model->isHidden());
    }
}
