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
 * @package     Magento_Widget
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Widget\Model;

class WidgetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Widget\Model\Widget
     */
    protected $_model = null;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Widget\Model\Widget'
        );
    }

    public function testGetWidgetsArray()
    {
        $declaredWidgets = $this->_model->getWidgetsArray();
        $this->assertNotEmpty($declaredWidgets);
        $this->assertInternalType('array', $declaredWidgets);
        foreach ($declaredWidgets as $row) {
            $this->assertArrayHasKey('name', $row);
            $this->assertArrayHasKey('code', $row);
            $this->assertArrayHasKey('type', $row);
            $this->assertArrayHasKey('description', $row);
        }
    }

    /**
     * @param string $type
     * @param string $expectedFile
     * @return string
     *
     * @dataProvider getPlaceholderImageUrlDataProvider
     * @magentoAppIsolation enabled
     */
    public function testGetPlaceholderImageUrl($type, $expectedFile)
    {
        $this->markTestIncomplete(
            'Functionality is failed because widget' .
            ' "app/design/frontend/magento_iphone_html5/etc/widget.xml" replaces' .
            ' "new_products" widget in Catalog module'
        );
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $objectManager->get('Magento\View\DesignInterface')->setDesignTheme('magento_backend');
        $expectedFilePath = "/adminhtml/magento_backend/en_US/{$expectedFile}";
        $expectedPubFile = $objectManager->get(
            'Magento\App\Filesystem'
        )->getPath(
            \Magento\App\Filesystem::STATIC_VIEW_DIR
        ) . $expectedFilePath;

        if (file_exists($expectedPubFile)) {
            unlink($expectedPubFile);
        }

        $url = $this->_model->getPlaceholderImageUrl($type);
        $this->assertStringEndsWith($expectedFile, $url);
        $this->assertFileExists($expectedPubFile);
        return $expectedPubFile;
    }

    /**
     * @return array
     */
    public function getPlaceholderImageUrlDataProvider()
    {
        return array(
            'custom image' => array(
                'Magento\Catalog\Block\Product\Widget\NewWidget',
                'Magento_Catalog/images/product_widget_new.gif'
            ),
            'default image' => array('non_existing_widget_type', 'Magento_Widget/placeholder.gif')
        );
    }

    /**
     * Tests, that theme file is found anywhere in theme folders, not only in module directory.
     *
     * @magentoDataFixture Magento/Widget/_files/themes.php
     * @magentoAppIsolation enabled
     */
    public function testGetPlaceholderImageUrlAtTheme()
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize(
            array(
                \Magento\App\Filesystem::PARAM_APP_DIRS => array(
                    \Magento\App\Filesystem::THEMES_DIR => array('path' => dirname(__DIR__) . '/_files/design')
                )
            )
        );
        $actualFile = $this->testGetPlaceholderImageUrl(
            'Magento\Catalog\Block\Product\Widget\NewWidget',
            'Magento_Catalog/images/product_widget_new.gif'
        );

        $expectedFile = dirname(
            __DIR__
        ) . '/_files/design/adminhtml/magento_backend/Magento_Catalog/images/product_widget_new.gif';
        $this->assertFileEquals($expectedFile, $actualFile);
    }
}
