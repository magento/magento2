<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;

/**
 * @magentoAppIsolation enabled
 * @magentoCache all disabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TranslateTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Translate */
    private $translate;

    protected function setUp()
    {
        /** @var \Magento\Framework\View\FileSystem $viewFileSystem */
        $viewFileSystem = $this->getMock(
            \Magento\Framework\View\FileSystem::class,
            ['getLocaleFileName', 'getDesignTheme'],
            [],
            '',
            false
        );

        $viewFileSystem->expects($this->any())
            ->method('getLocaleFileName')
            ->will(
                $this->returnValue(dirname(__DIR__) . '/Theme/Model/_files/design/frontend/Test/default/i18n/en_US.csv')
            );

        /** @var \Magento\Framework\View\Design\ThemeInterface $theme */
        $theme = $this->getMock(\Magento\Framework\View\Design\ThemeInterface::class, []);
        $theme->expects($this->any())->method('getId')->will($this->returnValue(10));

        $viewFileSystem->expects($this->any())->method('getDesignTheme')->will($this->returnValue($theme));

        /** @var \Magento\TestFramework\ObjectManager $objectManager */
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->addSharedInstance($viewFileSystem, \Magento\Framework\View\FileSystem::class);

        /** @var $moduleReader \Magento\Framework\Module\Dir\Reader */
        $moduleReader = $objectManager->get(\Magento\Framework\Module\Dir\Reader::class);
        $moduleReader->setModuleDir(
            'Magento_Store',
            'i18n',
            dirname(__DIR__) . '/Translation/Model/_files/Magento/Store/i18n'
        );
        $moduleReader->setModuleDir(
            'Magento_Catalog',
            'i18n',
            dirname(__DIR__) . '/Translation/Model/_files/Magento/Catalog/i18n'
        );

        /** @var \Magento\Theme\Model\View\Design $designModel */
        $designModel = $this->getMock(
            \Magento\Theme\Model\View\Design::class,
            ['getDesignTheme'],
            [
                $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class),
                $objectManager->get(\Magento\Framework\View\Design\Theme\FlyweightFactory::class),
                $objectManager->get(\Magento\Framework\App\Config\ScopeConfigInterface::class),
                $objectManager->get(\Magento\Theme\Model\ThemeFactory::class),
                $objectManager->get(\Magento\Framework\ObjectManagerInterface::class),
                $objectManager->get(\Magento\Framework\App\State::class),
                ['frontend' => 'Test/default']
            ]
        );

        $designModel->expects($this->any())->method('getDesignTheme')->will($this->returnValue($theme));

        $objectManager->addSharedInstance($designModel, \Magento\Theme\Model\View\Design\Proxy::class);

        $this->translate = $objectManager->create(\Magento\Framework\Translate::class);
        $objectManager->addSharedInstance($this->translate, \Magento\Framework\Translate::class);
        $objectManager->removeSharedInstance(\Magento\Framework\Phrase\Renderer\Composite::class);
        $objectManager->removeSharedInstance(\Magento\Framework\Phrase\Renderer\Translate::class);
        \Magento\Framework\Phrase::setRenderer(
            $objectManager->get(\Magento\Framework\Phrase\RendererInterface::class)
        );
    }

    public function testLoadData()
    {
        $data = $this->translate->loadData(null, true)->getData();
        CacheCleaner::cleanAll();
        $this->translate->loadData()->getData();
        $dataCached = $this->translate->loadData()->getData();
        $this->assertEquals($data, $dataCached);
    }

    /**
     * @magentoCache all disabled
     * @dataProvider translateDataProvider
     */
    public function testTranslate($inputText, $expectedTranslation)
    {
        $this->translate->loadData(\Magento\Framework\App\Area::AREA_FRONTEND);
        $actualTranslation = new \Magento\Framework\Phrase($inputText);
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
