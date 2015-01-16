<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\System\Configuration;

require_once realpath(
    __DIR__ . '/../../../../../../../../'
) . '/tools/Magento/Tools/Migration/System/Configuration/Mapper/AbstractMapper.php';
require_once realpath(
    __DIR__ . '/../../../../../../../../'
) . '/tools/Magento/Tools/Migration/System/Configuration/Mapper.php';
require_once realpath(
    __DIR__ . '/../../../../../../../../'
) . '/tools/Magento/Tools/Migration/System/Configuration/Mapper/Tab.php';
require_once realpath(
    __DIR__ . '/../../../../../../../../'
) . '/tools/Magento/Tools/Migration/System/Configuration/Mapper/Section.php';
/**
 * Test case for \Magento\Tools\Migration\System\Configuration\Mapper
 */
class MapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Migration\System\Configuration\Mapper
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_tabMapperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sectionMapperMock;

    protected function setUp()
    {
        $this->_tabMapperMock = $this->getMock(
            'Magento\Tools\Migration\System\Configuration\Mapper\Tab',
            [],
            [],
            '',
            false
        );
        $this->_sectionMapperMock = $this->getMock(
            'Magento\Tools\Migration\System\Configuration\Mapper\Section',
            [],
            [],
            '',
            false
        );

        $this->_object = new \Magento\Tools\Migration\System\Configuration\Mapper(
            $this->_tabMapperMock,
            $this->_sectionMapperMock
        );
    }

    protected function tearDown()
    {
        $this->_object = null;
        $this->_tabMapperMock = null;
        $this->_sectionMapperMock = null;
    }

    public function testTransformWithSetTabsAndSections()
    {
        $config = [
            'comment' => 'test comment',
            'tabs' => ['test tabs config'],
            'sections' => ['test sections config'],
        ];

        $this->_tabMapperMock->expects(
            $this->once()
        )->method(
            'transform'
        )->with(
            ['test tabs config']
        )->will(
            $this->returnArgument(0)
        );

        $this->_sectionMapperMock->expects(
            $this->once()
        )->method(
            'transform'
        )->with(
            ['test sections config']
        )->will(
            $this->returnArgument(0)
        );

        $expected = ['comment' => 'test comment', 'nodes' => ['test tabs config', 'test sections config']];
        $actual = $this->_object->transform($config);

        $this->assertEquals($expected, $actual);
    }

    public function testTransformWithoutSetTabsAndSections()
    {
        $config = ['comment' => 'test comment'];

        $this->_tabMapperMock->expects(
            $this->once()
        )->method(
            'transform'
        )->with(
            []
        )->will(
            $this->returnArgument(0)
        );

        $this->_sectionMapperMock->expects(
            $this->once()
        )->method(
            'transform'
        )->with(
            []
        )->will(
            $this->returnArgument(0)
        );

        $expected = ['comment' => 'test comment', 'nodes' => []];
        $actual = $this->_object->transform($config);

        $this->assertEquals($expected, $actual);
    }
}
