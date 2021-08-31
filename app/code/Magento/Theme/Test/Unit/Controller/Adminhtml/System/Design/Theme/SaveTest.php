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
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSaveAction(): void
    {
        $themeData = ['theme_id' => 123];
        $customCssContent = 'custom css content';
        $jsRemovedFiles = [3, 4];
        $jsOrder = [1 => '1', 2 => 'test'];

        $this->_request
            ->method('getParam')
            ->withConsecutive(
                ['back', false],
                ['theme'],
                ['custom_css_content'],
                ['js_removed_files'],
                ['js_order']
            )
            ->willReturnOnConsecutiveCalls(
                true,
                $themeData,
                $customCssContent,
                $jsRemovedFiles,
                $jsOrder
            );

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

        $this->_objectManagerMock
            ->method('get')
            ->withConsecutive([FlyweightFactory::class], [CustomCss::class])
            ->willReturnOnConsecutiveCalls($themeFactory, null);
        $this->_objectManagerMock
            ->method('create')
            ->with(SingleFile::class)
            ->willReturn(null);

        $this->_model->execute();
    }
}
