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
) . '/tools/Magento/Tools/Migration/System/Configuration/Mapper/Group.php';
require_once realpath(
    __DIR__ . '/../../../../../../../../../'
) . '/tools/Magento/Tools/Migration/System/Configuration/Mapper/Section.php';
/**
 * Test case for \Magento\Tools\Migration\System\Configuration\Mapper\Section
 */
class SectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_groupMapperMock;

    /**
     * @var \Magento\Tools\Migration\System\Configuration\Mapper\Section
     */
    protected $_object;

    protected function setUp()
    {
        $this->_groupMapperMock = $this->getMock(
            'Magento\Tools\Migration\System\Configuration\Mapper\Group',
            [],
            [],
            '',
            false
        );

        $this->_object = new \Magento\Tools\Migration\System\Configuration\Mapper\Section($this->_groupMapperMock);
    }

    protected function tearDown()
    {
        $this->_object = null;
        $this->_groupMapperMock = null;
    }

    public function testTransform()
    {
        $config = [
            'section_1' => [
                'sort_order' => ['#text' => 10],
                'frontend_type' => ['#text' => 'text'],
                'class' => ['#text' => 'css class'],
                'label' => ['#text' => 'section label'],
                'comment' => ['#cdata-section' => 'section comment'],
                'resource' => ['#text' => 'acl'],
                'header_css' => ['#text' => 'some css class'],
                'tab' => ['#text' => 'some tab'],
            ],
            'section_2' => [],
            'section_3' => ['groups' => ['label' => 'label']],
        ];

        $expected = [
            [
                'nodeName' => 'section',
                '@attributes' => ['id' => 'section_1', 'sortOrder' => 10, 'type' => 'text'],
                'parameters' => [
                    ['name' => 'class', '#text' => 'css class'],
                    ['name' => 'label', '#text' => 'section label'],
                    ['name' => 'comment', '#cdata-section' => 'section comment'],
                    ['name' => 'resource', '#text' => 'acl'],
                    ['name' => 'header_css', '#text' => 'some css class'],
                    ['name' => 'tab', '#text' => 'some tab'],
                ],
            ],
            ['nodeName' => 'section', '@attributes' => ['id' => 'section_2'], 'parameters' => []],
            [
                'nodeName' => 'section',
                '@attributes' => ['id' => 'section_3'],
                'parameters' => [],
                'subConfig' => ['label' => 'label']
            ],
        ];

        $this->_groupMapperMock->expects(
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
