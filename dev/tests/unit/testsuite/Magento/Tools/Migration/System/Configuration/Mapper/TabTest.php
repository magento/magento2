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
) . '/tools/Magento/Tools/Migration/System/Configuration/Mapper/Tab.php';
/**
 * Test case for \Magento\Tools\Migration\System\Configuration\Mapper\Tab
 */
class TabTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Migration\System\Configuration\Mapper\Tab
     */
    protected $_object;

    protected function setUp()
    {
        $this->_object = new \Magento\Tools\Migration\System\Configuration\Mapper\Tab();
    }

    protected function tearDown()
    {
        $this->_object = null;
    }

    public function testTransform()
    {
        $config = [
            'tab_1' => [
                'sort_order' => ['#text' => 10],
                'frontend_type' => ['#text' => 'text'],
                'class' => ['#text' => 'css class'],
                'label' => ['#text' => 'tab label'],
                'comment' => ['#cdata-section' => 'tab comment'],
            ],
            'tab_2' => [],
        ];

        $expected = [
            [
                'nodeName' => 'tab',
                '@attributes' => ['id' => 'tab_1', 'sortOrder' => 10, 'type' => 'text', 'class' => 'css class'],
                'parameters' => [
                    ['name' => 'label', '#text' => 'tab label'],
                    ['name' => 'comment', '#cdata-section' => 'tab comment'],
                ],
            ],
            ['nodeName' => 'tab', '@attributes' => ['id' => 'tab_2'], 'parameters' => []],
        ];

        $actual = $this->_object->transform($config);
        $this->assertEquals($expected, $actual);
    }
}
