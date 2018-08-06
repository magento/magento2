<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\CacheCleaner;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * @magentoAppIsolation enabled
 * @magentoCache all disabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TranslateTest extends TestCase
{
    /** @var \Magento\Framework\Translate */
    private $translate;

    protected function setUp()
    {
        /** @var \Magento\Framework\View\FileSystem | PHPUnit_Framework_MockObject_MockObject $viewFileSystem */
        $viewFileSystem = $this->createPartialMock(
            \Magento\Framework\View\FileSystem::class,
            ['getLocaleFileName']
        );

        $viewFileSystem->expects($this->any())
            ->method('getLocaleFileName')
            ->will(
                $this->returnValue(
                    dirname(__DIR__) . '/Translation/Model/_files/Magento/design/Magento/theme/i18n/en_US.csv'
                )
            );

        /** @var \Magento\Framework\View\Design\ThemeInterface | PHPUnit_Framework_MockObject_MockObject $theme */
        $theme = $this->createMock(\Magento\Framework\View\Design\ThemeInterface::class);
        $theme->expects($this->any())->method('getThemePath')->will($this->returnValue('Magento/luma'));

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

        /** @var \Magento\Theme\Model\View\Design | \PHPUnit_Framework_MockObject_MockObject $designModel */
        $designModel = $this->getMockBuilder(\Magento\Theme\Model\View\Design::class)
            ->setMethods(['getDesignTheme'])
            ->setConstructorArgs(
                [
                    $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class),
                    $objectManager->get(\Magento\Framework\View\Design\Theme\FlyweightFactory::class),
                    $objectManager->get(\Magento\Framework\App\Config\ScopeConfigInterface::class),
                    $objectManager->get(\Magento\Theme\Model\ThemeFactory::class),
                    $objectManager->get(\Magento\Framework\ObjectManagerInterface::class),
                    $objectManager->get(\Magento\Framework\App\State::class),
                    ['frontend' => 'Test/default']
                ]
            )
            ->getMock();

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
     * @param string $inputText
     * @param string $expectedTranslation
     * @throws Exception\LocalizedException
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
            [
                'Theme phrase will be translated',
                'Theme phrase is translated',
            ],
            [
                'Phrase in Magento_Store module that doesn\'t need translation',
                'Phrase in Magento_Store module that doesn\'t need translation',
            ],
            [
                'Phrase in Magento_Catalog module that doesn\'t need translation',
                'Phrase in Magento_Catalog module that doesn\'t need translation',
            ],
            [
                'Magento_Store module phrase will be override by theme translation',
                'Magento_Store module phrase is override by theme translation',
            ],
            [
                'Magento_Catalog module phrase will be override by theme translation',
                'Magento_Catalog module phrase is override by theme translation',
            ],
        ];
    }
}
