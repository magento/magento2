<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework;

/**
 * @magentoDataFixture Magento/Backend/controllers/_files/cache/all_types_disabled.php
 */
class TranslateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Translate
     */
    protected $_model;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $_designModel;

    /**
     * @var \Magento\Framework\View\FileSystem
     */
    protected $_viewFileSystem;

    protected function setUp()
    {
        $this->_viewFileSystem = $this->getMock(
            'Magento\Framework\View\FileSystem',
            array('getLocaleFileName', 'getDesignTheme'),
            array(),
            '',
            false
        );

        $this->_viewFileSystem->expects($this->any())
            ->method(
                'getLocaleFileName'
            )->will(
                $this->returnValue(dirname(__DIR__) . '/Core/Model/_files/design/frontend/test_default/i18n/en_US.csv')
            );

        $theme = $this->getMock('\Magento\Framework\View\Design\ThemeInterface', array());
        $theme->expects($this->any())->method('getId')->will($this->returnValue(10));

        $this->_viewFileSystem->expects($this->any())->method('getDesignTheme')->will($this->returnValue($theme));

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->addSharedInstance($this->_viewFileSystem, 'Magento\Framework\View\FileSystem');

        /** @var $moduleReader \Magento\Framework\Module\Dir\Reader */
        $moduleReader = $objectManager->get('Magento\Framework\Module\Dir\Reader');
        $moduleReader->setModuleDir('Magento_Core', 'i18n', dirname(__DIR__) . '/Core/Model/_files/Magento/Core/i18n');
        $moduleReader->setModuleDir(
            'Magento_Catalog',
            'i18n',
            dirname(__DIR__) . '/Core/Model/_files/Magento/Catalog/i18n'
        );

        /** @var \Magento\Core\Model\View\Design _designModel */
        $this->_designModel = $this->getMock(
            'Magento\Core\Model\View\Design',
            array('getDesignTheme'),
            array(
                $objectManager->get('Magento\Framework\StoreManagerInterface'),
                $objectManager->get('Magento\Framework\View\Design\Theme\FlyweightFactory'),
                $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface'),
                $objectManager->get('Magento\Core\Model\ThemeFactory'),
                $objectManager->get('Magento\Framework\Locale\ResolverInterface'),
                $objectManager->get('Magento\Framework\App\State'),
                array('frontend' => 'test_default')
            )
        );

        $this->_designModel->expects($this->any())->method('getDesignTheme')->will($this->returnValue($theme));

        $objectManager->addSharedInstance($this->_designModel, 'Magento\Core\Model\View\Design\Proxy');

        $this->_model = $objectManager->create('Magento\Framework\Translate');
        $objectManager->addSharedInstance($this->_model, 'Magento\Framework\Translate');
        $objectManager->removeSharedInstance('Magento\Framework\Phrase\Renderer\Composite');
        $objectManager->removeSharedInstance('Magento\Framework\Phrase\Renderer\Translate');
        \Magento\Framework\Phrase::setRenderer($objectManager->get('Magento\Framework\Phrase\RendererInterface'));
        $this->_model->loadData(\Magento\Framework\App\Area::AREA_FRONTEND);
    }

    /**
     * @magentoDataFixture Magento/Translation/_files/db_translate.php
     * @magentoDataFixture Magento/Backend/controllers/_files/cache/all_types_enabled.php
     * @covers \Magento\Translation\Model\Resource\Translate::getStoreId
     * @covers \Magento\Translation\Model\Resource\String::getStoreId
     */
    public function testLoadDataCaching()
    {
        /** @var \Magento\Translation\Model\Resource\String $translateString */
        $translateString = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Translation\Model\Resource\String'
        );
        $translateString->saveTranslate('Fixture String', 'New Db Translation');

        $this->_model->loadData(\Magento\Framework\App\Area::AREA_FRONTEND);
        $this->assertEquals(
            'Fixture Db Translation', 
            __('Fixture String'), 
            'Translation is expected to be cached'
        );

        $this->_model->loadData(\Magento\Framework\App\Area::AREA_FRONTEND, true);
        $this->assertEquals(
            'New Db Translation', 
            __('Fixture String'), 
            'Forced load should not use cache'
        );
    }

    /**
     * @magentoAppIsolation enabled
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
        return array(
            array('', ''),
            array('Text with different translation on different modules', 'Text translation that was last loaded'),
            array('text_with_no_translation', 'text_with_no_translation'),
            array('Design value to translate', 'Design translated value')
        );
    }
}
