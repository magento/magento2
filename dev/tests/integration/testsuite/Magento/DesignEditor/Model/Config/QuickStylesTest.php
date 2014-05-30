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
namespace Magento\DesignEditor\Model\Config;

class QuickStylesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\DesignEditor\Model\Config\Control\QuickStyles
     */
    protected $_model;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $_design;

    /**
     * Initialize dependencies
     */
    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Framework\App\Filesystem\DirectoryList $directoryList */
        $directoryList = $objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
        $path = str_replace($directoryList->getRoot(), '', str_replace('\\', '/', __DIR__) . '/../_files/design');
        $directoryList->addDirectory(\Magento\Framework\App\Filesystem::THEMES_DIR, array('path' => ltrim($path, '/')));
        $this->_design = $objectManager->get('Magento\Framework\View\DesignInterface');
        $objectManager->get('Magento\Framework\App\State')
            ->setAreaCode(\Magento\Framework\View\DesignInterface::DEFAULT_AREA);
        $this->_design->setDesignTheme('vendor_test');
        /** @var \Magento\Framework\View\Asset\Repository $assetRepo */
        $assetRepo = $objectManager->get('Magento\Framework\View\Asset\Repository');
        $quickStylesPath = $assetRepo->createAsset('Magento_DesignEditor::controls/quick_styles.xml')->getSourceFile();
        $this->assertFileExists($quickStylesPath);
        $this->_model = $objectManager->create(
            'Magento\DesignEditor\Model\Config\Control\QuickStyles',
            array('configFiles' => array(file_get_contents($quickStylesPath)))
        );
    }

    /**
     * Test control data
     *
     * @magentoDataFixture Magento/DesignEditor/Model/_files/design/themes.php
     * @dataProvider getTestDataProvider
     * @magentoAppIsolation enabled
     * @param string $controlName
     * @param array $expectedControlData
     */
    public function testLoadConfiguration($controlName, $expectedControlData)
    {
        $this->assertEquals($expectedControlData, $this->_model->getControlData($controlName));
    }

    /**
     * Data provider with sample data for test controls
     *
     * @return array
     */
    public function getTestDataProvider()
    {
        return array(
            array(
                'headers',
                array(
                    'type' => 'logo',
                    'layoutParams' => array('title' => 'Headers', 'column' => 'left'),
                    'components' => array(
                        'logo-picker' => array(
                            'type' => 'color-picker',
                            'selector' => '.body .div',
                            'attribute' => 'background-color',
                            'var' => 'Magento_DesignEditor::test_var_key1'
                        ),
                        'font-selector' => array(
                            'type' => 'font-selector',
                            'selector' => '*',
                            'attribute' => 'font-family',
                            'options' => array('Arial, Verdana, Georgia', 'Tahoma'),
                            'var' => 'Magento_DesignEditor::test_var_key2'
                        ),
                        'test-control' => array(
                            'type' => 'test-control',
                            'components' => array(
                                'image-uploader' => array(
                                    'type' => 'logo-uploader',
                                    'selector' => '.test-logo-1',
                                    'attribute' => 'background-image',
                                    'var' => 'Magento_DesignEditor::test_var_key3'
                                )
                            )
                        )
                    )
                )
            ),
            array(
                'logo-uploader',
                array(
                    'type' => 'logo-uploader',
                    'selector' => '.test-logo-2',
                    'attribute' => 'background-image',
                    'layoutParams' => array('title' => 'Logo Uploader', 'column' => 'center'),
                    'var' => 'Magento_DesignEditor::test_var_key4'
                )
            ),
            array(
                'background-color-picker',
                array(
                    'type' => 'color-picker',
                    'layoutParams' => array('title' => 'Background Color', 'column' => 'right'),
                    'selector' => '.body .div',
                    'attribute' => 'background-color',
                    'var' => 'Magento_DesignEditor::test_var_key5'
                )
            )
        );
    }
}
