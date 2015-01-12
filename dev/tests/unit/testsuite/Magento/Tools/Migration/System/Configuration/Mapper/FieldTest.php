<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\System\Configuration\Mapper;

require_once realpath(
    __DIR__ . '/../../../../../../../../../'
) . '/tools/Magento/Tools/Migration/System/Configuration/Mapper/AbstractMapper.php';
require_once realpath(
    __DIR__ . '/../../../../../../../../../'
) . '/tools/Magento/Tools/Migration/System/Configuration/Mapper/Field.php';
/**
 * Test case for Tools_Migration_System_Configuration_Mapper_Field
 */
class FieldTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Migration\System\Configuration\Mapper\Field
     */
    protected $_object;

    protected function setUp()
    {
        $this->_object = new \Magento\Tools\Migration\System\Configuration\Mapper\Field();
    }

    protected function tearDown()
    {
        $this->_object = null;
    }

    public function testTransform()
    {
        $config = [
            'field_1' => [
                'comment' => ['#cdata-section' => 'comment_test'],
                'tooltip' => ['#text' => 'tooltip_test'],
                'frontend_class' => ['#text' => 'frontend_class_test'],
                'validate' => ['#text' => 'validate_test'],
                'can_be_empty' => ['#text' => 'can_be_empty_test'],
                'if_module_enabled' => ['#text' => 'if_module_enabled_test'],
                'frontend_model' => ['#text' => 'frontend_model_test'],
                'backend_model' => ['#text' => 'backend_model_test'],
                'source_model' => ['#text' => 'source_model_test'],
                'config_path' => ['#text' => 'config_path_test'],
                'base_url' => ['#text' => 'base_url_test'],
                'upload_dir' => ['#text' => 'upload_dir_test'],
                'button_url' => ['#text' => 'button_url_test'],
                'button_label' => ['#text' => 'button_label_test'],
                'depends' => ['module1' => ['#text' => 'yes']],
                'more_url' => ['#text' => 'more_url_test'],
                'demo_url' => ['#text' => 'demo_url_test'],
                'undefined' => ['#text' => 'undefined_test', '@attributes' => ['some' => 'attribute']],
                'node' => ['label' => ['nodeLabel' => ['#text' => 'nodeValue']]],
            ],
        ];

        $expected = [
            [
                'nodeName' => 'field',
                '@attributes' => ['id' => 'field_1'],
                'parameters' => [
                    ['name' => 'comment', '#cdata-section' => 'comment_test'],
                    ['name' => 'tooltip', '#text' => 'tooltip_test'],
                    ['name' => 'frontend_class', '#text' => 'frontend_class_test'],
                    ['name' => 'validate', '#text' => 'validate_test'],
                    ['name' => 'can_be_empty', '#text' => 'can_be_empty_test'],
                    ['name' => 'if_module_enabled', '#text' => 'if_module_enabled_test'],
                    ['name' => 'frontend_model', '#text' => 'frontend_model_test'],
                    ['name' => 'backend_model', '#text' => 'backend_model_test'],
                    ['name' => 'source_model', '#text' => 'source_model_test'],
                    ['name' => 'config_path', '#text' => 'config_path_test'],
                    ['name' => 'base_url', '#text' => 'base_url_test'],
                    ['name' => 'upload_dir', '#text' => 'upload_dir_test'],
                    ['name' => 'button_url', '#text' => 'button_url_test'],
                    ['name' => 'button_label', '#text' => 'button_label_test'],
                    [
                        'name' => 'depends',
                        'subConfig' => [
                            ['nodeName' => 'field', '@attributes' => ['id' => 'module1'], '#text' => 'yes'],
                        ]
                    ],
                    ['name' => 'more_url', '#text' => 'more_url_test'],
                    ['name' => 'demo_url', '#text' => 'demo_url_test'],
                    [
                        '@attributes' => ['type' => 'undefined', 'some' => 'attribute'],
                        'name' => 'attribute',
                        '#text' => 'undefined_test'
                    ],
                    [
                        '@attributes' => ['type' => 'node'],
                        'name' => 'attribute',
                        'subConfig' => [
                            [
                                'nodeName' => 'label',
                                'subConfig' => [['nodeName' => 'nodeLabel', '#text' => 'nodeValue']],
                            ],
                        ]
                    ],
                ],
            ],
        ];

        $actual = $this->_object->transform($config);
        $this->assertEquals($expected, $actual);
    }
}
