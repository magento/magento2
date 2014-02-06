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
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento;

/**
 * @magentoDataFixture Magento/Backend/controllers/_files/cache/all_types_disabled.php
 */
class TranslateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Translate
     */
    protected $_model;

    /**
     * @var \Magento\View\DesignInterface
     */
    protected $_designModel;

    /**
     * @var \Magento\View\FileSystem
     */
    protected $_viewFileSystem;

    protected function setUp()
    {
        $pathChunks = array(
            __DIR__,
            'Core',
            'Model',
            '_files',
            'design',
            'frontend',
            'test_default',
            'i18n',
            'en_US.csv'
        );

        $this->_viewFileSystem = $this->getMock('Magento\View\FileSystem',
            array('getFilename', 'getDesignTheme'), array(), '', false);

        $this->_viewFileSystem->expects($this->any())
            ->method('getFilename')
            ->will($this->returnValue(implode('/', $pathChunks)));

        $theme = $this->getMock('\Magento\View\Design\ThemeInterface', array());
        $theme->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(10));

        $this->_viewFileSystem->expects($this->any())
            ->method('getDesignTheme')
            ->will($this->returnValue($theme));

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->addSharedInstance($this->_viewFileSystem, 'Magento\View\FileSystem');

        /** @var $moduleReader \Magento\Module\Dir\Reader */
        $moduleReader = $objectManager->get('Magento\Module\Dir\Reader');
        $moduleReader->setModuleDir('Magento_Core', 'i18n', __DIR__ . '/Core/Model/_files/Magento/Core/i18n');
        $moduleReader->setModuleDir('Magento_Catalog', 'i18n',
            __DIR__ . '/Core/Model/_files/Magento/Catalog/i18n');

        /** @var \Magento\Core\Model\View\Design _designModel */
        $this->_designModel = $this->getMock('Magento\Core\Model\View\Design',
            array('getDesignTheme'),
            array(
                $objectManager->get('Magento\Core\Model\StoreManagerInterface'),
                $objectManager->get('Magento\View\Design\Theme\FlyweightFactory'),
                $objectManager->get('Magento\App\ConfigInterface'),
                $objectManager->get('Magento\Core\Model\Store\Config'),
                $objectManager->get('Magento\Core\Model\ThemeFactory'),
                $objectManager->get('Magento\Core\Model\App'),
                $objectManager->get('Magento\App\State'),
                array('frontend' => 'test_default')
            )
        );

        $this->_designModel->expects($this->any())
            ->method('getDesignTheme')
            ->will($this->returnValue($theme));

        $objectManager->addSharedInstance($this->_designModel, 'Magento\Core\Model\View\Design\Proxy');

        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Translate');
        $this->_model->init(\Magento\Core\Model\App\Area::AREA_FRONTEND);
    }

    /**
     * @magentoDataFixture Magento/Core/_files/db_translate.php
     * @magentoDataFixture Magento/Backend/controllers/_files/cache/all_types_enabled.php
     * @covers \Magento\Backend\Model\Resource\Translate::_getStoreId
     * @covers \Magento\Backend\Model\Resource\Translate\String::_getStoreId
     */
    public function testInitCaching()
    {
        // ensure string translation is cached
        $this->_model->init(\Magento\Core\Model\App\Area::AREA_FRONTEND, null);

        /** @var \Magento\Core\Model\Resource\Translate\String $translateString */
        $translateString = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\Resource\Translate\String');
        $translateString->saveTranslate('Fixture String', 'New Db Translation');

        $this->_model->init(\Magento\Core\Model\App\Area::AREA_FRONTEND, null);
        $this->assertEquals(
            'Fixture Db Translation', $this->_model->translate(array('Fixture String')),
            'Translation is expected to be cached'
        );

        $this->_model->init(\Magento\Core\Model\App\Area::AREA_FRONTEND, null, true);
        $this->assertEquals(
            'New Db Translation', $this->_model->translate(array('Fixture String')),
            'Forced load should not use cache'
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @dataProvider translateDataProvider
     */
    public function testTranslate($inputText, $expectedTranslation)
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Translate');
        $this->_model->init(\Magento\Core\Model\App\Area::AREA_FRONTEND);

        $actualTranslation = $this->_model->translate(array($inputText));
        $this->assertEquals($expectedTranslation, $actualTranslation);
    }

    /**
     * @return array
     */
    public function translateDataProvider()
    {
        return array(
            array('', ''),
            array(
                'Text with different translation on different modules',
                'Text translation that was last loaded'
            ),
            array(
                'text_with_no_translation',
                'text_with_no_translation'
            ),
            array(
                'Design value to translate',
                'Design translated value'
            )
        );
    }

    public function testGetSetTranslateInline()
    {
        $this->assertEquals(true, $this->_model->getTranslateInline());
        $this->_model->setTranslateInline(false);
        $this->assertEquals(false, $this->_model->getTranslateInline());
    }
}
