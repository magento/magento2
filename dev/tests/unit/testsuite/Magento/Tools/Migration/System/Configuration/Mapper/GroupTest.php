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
require_once realpath(
    __DIR__ . '/../../../../../../../../../'
) . '/tools/Magento/Tools/Migration/System/Configuration/Mapper/Group.php';
/**
 * Test case for \Magento\Tools\Migration\System\Configuration\Mapper\Group
 */
class GroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fieldMapperMock;

    /**
     * @var \Magento\Tools\Migration\System\Configuration\Mapper\Group
     */
    protected $_object;

    protected function setUp()
    {
        $this->_fieldMapperMock = $this->getMock(
            'Magento\Tools\Migration\System\Configuration\Mapper\Field',
            [],
            [],
            '',
            false
        );

        $this->_object = new \Magento\Tools\Migration\System\Configuration\Mapper\Group($this->_fieldMapperMock);
    }

    protected function tearDown()
    {
        $this->_object = null;
        $this->_fieldMapperMock = null;
    }

    public function testTransform()
    {
        $config = [
            'group_1' => [
                'sort_order' => ['#text' => 10],
                'frontend_type' => ['#text' => 'text'],
                'class' => ['#text' => 'css class'],
                'label' => ['#text' => 'group label'],
                'comment' => ['#cdata-section' => 'group comment'],
                'resource' => ['#text' => 'acl'],
                'fieldset_css' => ['#text' => 'some css class'],
                'clone_fields' => ['#text' => 'some fields'],
                'clone_model' => ['#text' => 'some model'],
                'help_url' => ['#text' => 'some url'],
                'hide_in_single_store_mode' => ['#text' => 'mode'],
                'expanded' => ['#text' => 'yes'],
            ],
            'group_2' => [],
            'group_3' => ['fields' => ['label' => 'label']],
        ];

        $expected = [
            [
                'nodeName' => 'group',
                '@attributes' => ['id' => 'group_1', 'sortOrder' => 10, 'type' => 'text'],
                'parameters' => [
                    ['name' => 'class', '#text' => 'css class'],
                    ['name' => 'label', '#text' => 'group label'],
                    ['name' => 'comment', '#cdata-section' => 'group comment'],
                    ['name' => 'resource', '#text' => 'acl'],
                    ['name' => 'fieldset_css', '#text' => 'some css class'],
                    ['name' => 'clone_fields', '#text' => 'some fields'],
                    ['name' => 'clone_model', '#text' => 'some model'],
                    ['name' => 'help_url', '#text' => 'some url'],
                    ['name' => 'hide_in_single_store_mode', '#text' => 'mode'],
                    ['name' => 'expanded', '#text' => 'yes'],
                ],
            ],
            ['nodeName' => 'group', '@attributes' => ['id' => 'group_2'], 'parameters' => []],
            [
                'nodeName' => 'group',
                '@attributes' => ['id' => 'group_3'],
                'parameters' => [],
                'subConfig' => ['label' => 'label']
            ],
        ];

        $this->_fieldMapperMock->expects(
            $this->once()
        )->method(
            'transform'
        )->with(
            ['label' => 'label']
        )->will(
            $this->returnArgument(0)
        );

        $actual = $this->_object->transform($config);
        $this->assertEquals($expected, $actual);
    }
}
