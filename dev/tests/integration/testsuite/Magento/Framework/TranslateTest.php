<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoCache all disabled
 */
class TranslateTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        /** @var \Magento\Framework\View\FileSystem $viewFileSystem */
        $viewFileSystem = $this->getMock(
            'Magento\Framework\View\FileSystem',
            ['getLocaleFileName', 'getDesignTheme'],
            [],
            '',
            false
        );

        $viewFileSystem->expects($this->any())
            ->method('getLocaleFileName')
            ->will(
                $this->returnValue(dirname(__DIR__) . '/Core/Model/_files/design/frontend/Test/default/i18n/en_US.csv')
            );

        /** @var \Magento\Framework\View\Design\ThemeInterface $theme */
        $theme = $this->getMock('Magento\Framework\View\Design\ThemeInterface', []);
        $theme->expects($this->any())->method('getId')->will($this->returnValue(10));

        $viewFileSystem->expects($this->any())->method('getDesignTheme')->will($this->returnValue($theme));

        $objectManager = Bootstrap::getObjectManager();
        $objectManager->addSharedInstance($viewFileSystem, 'Magento\Framework\View\FileSystem');

        /** @var $moduleReader \Magento\Framework\Module\Dir\Reader */
        $moduleReader = $objectManager->get('Magento\Framework\Module\Dir\Reader');
        $moduleReader->setModuleDir('Magento_Core', 'i18n', dirname(__DIR__) . '/Core/Model/_files/Magento/Core/i18n');
        $moduleReader->setModuleDir(
            'Magento_Catalog',
            'i18n',
            dirname(__DIR__) . '/Core/Model/_files/Magento/Catalog/i18n'
        );

        /** @var \Magento\Core\Model\View\Design $designModel */
        $designModel = $this->getMock(
            'Magento\Core\Model\View\Design',
            ['getDesignTheme'],
            [
                $objectManager->get('Magento\Store\Model\StoreManagerInterface'),
                $objectManager->get('Magento\Framework\View\Design\Theme\FlyweightFactory'),
                $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface'),
                $objectManager->get('Magento\Core\Model\ThemeFactory'),
                $objectManager->get('Magento\Framework\ObjectManagerInterface'),
                $objectManager->get('Magento\Framework\App\State'),
                ['frontend' => 'Test/default']
            ]
        );

        $designModel->expects($this->any())->method('getDesignTheme')->will($this->returnValue($theme));

        $objectManager->addSharedInstance($designModel, 'Magento\Core\Model\View\Design\Proxy');

        $model = $objectManager->create('Magento\Framework\Translate');
        $objectManager->addSharedInstance($model, 'Magento\Framework\Translate');
        $objectManager->removeSharedInstance('Magento\Framework\Phrase\Renderer\Composite');
        $objectManager->removeSharedInstance('Magento\Framework\Phrase\Renderer\Translate');
        \Magento\Framework\Phrase::setRenderer($objectManager->get('Magento\Framework\Phrase\RendererInterface'));
        $model->loadData(\Magento\Framework\App\Area::AREA_FRONTEND);
    }

    /**
     * @dataProvider translateDataProvider
     */
    public function testTranslate($inputText, $expectedTranslation)
    {
        $actualTranslation = __($inputText);
        $this->assertEquals($expectedTranslation, $actualTranslation);
    }

    /**
     * @return array
     */
    public function translateDataProvider()
    {
        return [
            ['', ''],
            ['Text with different translation on different modules', 'Text translation that was last loaded'],
            ['text_with_no_translation', 'text_with_no_translation'],
            ['Design value to translate', 'Design translated value']
        ];
    }
}
