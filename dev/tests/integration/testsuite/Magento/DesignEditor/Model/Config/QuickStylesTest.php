<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        $this->_design = $objectManager->get('Magento\Framework\View\DesignInterface');
        $objectManager->get('Magento\Framework\App\State')
            ->setAreaCode(\Magento\Framework\View\DesignInterface::DEFAULT_AREA);
        $this->_design->setDesignTheme('Vendor/test');
        /** @var \Magento\Framework\View\Asset\Repository $assetRepo */
        $assetRepo = $objectManager->get('Magento\Framework\View\Asset\Repository');
        $quickStylesPath = $assetRepo->createAsset('Magento_DesignEditor::controls/quick_styles.xml')->getSourceFile();
        $this->assertFileExists($quickStylesPath);
        $this->_model = $objectManager->create(
            'Magento\DesignEditor\Model\Config\Control\QuickStyles',
            ['configFiles' => [file_get_contents($quickStylesPath)]]
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
        return [
            [
                'headers',
                [
                    'type' => 'logo',
                    'layoutParams' => ['title' => 'Headers', 'column' => 'left'],
                    'components' => [
                        'logo-picker' => [
                            'type' => 'color-picker',
                            'selector' => '.body .div',
                            'attribute' => 'background-color',
                            'var' => 'Magento_DesignEditor::test_var_key1',
                        ],
                        'font-selector' => [
                            'type' => 'font-selector',
                            'selector' => '*',
                            'attribute' => 'font-family',
                            'options' => ['Arial, Verdana, Georgia', 'Tahoma'],
                            'var' => 'Magento_DesignEditor::test_var_key2',
                        ],
                        'test-control' => [
                            'type' => 'test-control',
                            'components' => [
                                'image-uploader' => [
                                    'type' => 'logo-uploader',
                                    'selector' => '.test-logo-1',
                                    'attribute' => 'background-image',
                                    'var' => 'Magento_DesignEditor::test_var_key3',
                                ],
                            ],
                        ],
                    ]
                ],
            ],
            [
                'logo-uploader',
                [
                    'type' => 'logo-uploader',
                    'selector' => '.test-logo-2',
                    'attribute' => 'background-image',
                    'layoutParams' => ['title' => 'Logo Uploader', 'column' => 'center'],
                    'var' => 'Magento_DesignEditor::test_var_key4'
                ]
            ],
            [
                'background-color-picker',
                [
                    'type' => 'color-picker',
                    'layoutParams' => ['title' => 'Background Color', 'column' => 'right'],
                    'selector' => '.body .div',
                    'attribute' => 'background-color',
                    'var' => 'Magento_DesignEditor::test_var_key5'
                ]
            ]
        ];
    }
}
