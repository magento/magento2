<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Structure\Element;

class FlyweightFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Config\Model\Config\Structure\Element\FlyweightFactory
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_model = new \Magento\Config\Model\Config\Structure\Element\FlyweightFactory(
            $this->_objectManagerMock
        );
    }

    protected function tearDown()
    {
        unset($this->_model);
        unset($this->_objectManagerMock);
    }

    public function testCreate()
    {
        $this->_objectManagerMock->expects(
            $this->any()
        )->method(
            'create'
        )->will(
            $this->returnValueMap(
                [
                    [\Magento\Config\Model\Config\Structure\Element\Section::class, [], 'sectionObject'],
                    [\Magento\Config\Model\Config\Structure\Element\Group::class, [], 'groupObject'],
                    [\Magento\Config\Model\Config\Structure\Element\Field::class, [], 'fieldObject'],
                ]
            )
        );
        $this->assertEquals('sectionObject', $this->_model->create('section'));
        $this->assertEquals('groupObject', $this->_model->create('group'));
        $this->assertEquals('fieldObject', $this->_model->create('field'));
    }
}
