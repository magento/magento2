<?php declare(strict_types=1);
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
            ->will($this->returnValue(true));

        $this->_request->expects($this->at(1))
            ->method('getParam')
            ->with('theme')
            ->will($this->returnValue($themeData));

        $this->_request->expects($this->at(2))
            ->method('getParam')
            ->with('custom_css_content')
            ->will($this->returnValue($customCssContent));

        $this->_request->expects($this->at(3))
            ->method('getParam')
            ->with('js_removed_files')
            ->will($this->returnValue($jsRemovedFiles));

        $this->_request->expects($this->at(4))
            ->method('getParam')
            ->with('js_order')
            ->will($this->returnValue($jsOrder));

        $this->_request->expects($this->once(5))->method('getPostValue')->will($this->returnValue(true));

        $themeMock = $this->createPartialMock(
            Theme::class,
            ['save', 'load', 'setCustomization', 'getThemeImage', '__wakeup']
        );

        $themeImage = $this->createMock(Data::class);
        $themeMock->expects($this->any())->method('getThemeImage')->will($this->returnValue($themeImage));

        $themeFactory = $this->createPartialMock(
            FlyweightFactory::class,
            ['create']
        );
        $themeFactory->expects($this->once())->method('create')->will($this->returnValue($themeMock));

        $this->_objectManagerMock->expects($this->at(0))
            ->method('get')
            ->with(FlyweightFactory::class)
            ->will($this->returnValue($themeFactory));

        $this->_objectManagerMock->expects($this->at(1))
            ->method('get')
            ->with(CustomCss::class)
            ->will($this->returnValue(null));

        $this->_objectManagerMock->expects($this->at(2))
            ->method('create')
            ->with(SingleFile::class)
            ->will($this->returnValue(null));

        $this->_model->execute();
    }
}
