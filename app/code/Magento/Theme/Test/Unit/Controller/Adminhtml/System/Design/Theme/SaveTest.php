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
use Magento\Theme\Test\Unit\Controller\Adminhtml\System\Design\ThemeTestCase;

class SaveTest extends ThemeTestCase
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
            ->willReturnCallback(function ($arg1, $arg2)
 use ($themeData, $customCssContent, $jsRemovedFiles, $jsOrder) {
                if ($arg1 == 'back' && $arg2 === false) {
                    return true;
                } elseif ($arg1 == 'theme') {
                    return $themeData;
                } elseif ($arg1 == 'custom_css_content') {
                    return $customCssContent;
                } elseif ($arg1 == 'js_removed_files') {
                    return $jsRemovedFiles;
                } elseif ($arg1 == 'js_order') {
                    return $jsOrder;
                }
            });

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
            ->willReturnCallback(fn($param) => match ([$param]) {
                [FlyweightFactory::class] => $themeFactory,
                [CustomCss::class] => null
            });
        $this->_objectManagerMock
            ->method('create')
            ->with(SingleFile::class)
            ->willReturn(null);

        $this->_model->execute();
    }
}
