<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\Theme;

use Magento\Framework\View\Design\Theme\FlyweightFactory;
use Magento\Theme\Model\Theme;
use Magento\Theme\Model\Theme\Customization\File\CustomCss;
use Magento\Theme\Model\Theme\Data;
use Magento\Theme\Model\Theme\SingleFile;
use Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\ThemeTest;

class SaveTest extends ThemeTest
{
    /**
     * @var string
     */
    protected $name = 'Save';

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSaveAction()
    {
        $themeData = ['theme_id' => 123];
        $customCssContent = 'custom css content';
        $jsRemovedFiles = [3, 4];
        $jsOrder = [1 => '1', 2 => 'test'];

        $this->_request->expects($this->at(0))
            ->method('getParam')
            ->with('back', false)
            ->willReturn(true);

        $this->_request->expects($this->at(1))
            ->method('getParam')
            ->with('theme')
            ->willReturn($themeData);

        $this->_request->expects($this->at(2))
            ->method('getParam')
            ->with('custom_css_content')
            ->willReturn($customCssContent);

        $this->_request->expects($this->at(3))
            ->method('getParam')
            ->with('js_removed_files')
            ->willReturn($jsRemovedFiles);

        $this->_request->expects($this->at(4))
            ->method('getParam')
            ->with('js_order')
            ->willReturn($jsOrder);

        $this->_request->expects($this->once())->method('getPostValue')->willReturn(true);

        $themeMock = $this->getMockBuilder(Theme::class)
            ->addMethods(['setCustomization'])
            ->onlyMethods(['save', 'load', 'getThemeImage', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();

        $themeImage = $this->createMock(Data::class);
        $themeMock->expects($this->any())->method('getThemeImage')->willReturn($themeImage);

        $themeFactory = $this->createPartialMock(
            FlyweightFactory::class,
            ['create']
        );
        $themeFactory->expects($this->once())->method('create')->willReturn($themeMock);

        $this->_objectManagerMock->expects($this->at(0))
            ->method('get')
            ->with(FlyweightFactory::class)
            ->willReturn($themeFactory);

        $this->_objectManagerMock->expects($this->at(1))
            ->method('get')
            ->with(CustomCss::class)
            ->willReturn(null);

        $this->_objectManagerMock->expects($this->at(2))
            ->method('create')
            ->with(SingleFile::class)
            ->willReturn(null);

        $this->_model->execute();
    }
}
