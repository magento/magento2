<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for entity source model \Magento\ImportExport\Model\Source\Import\Entity
 */
namespace Magento\ImportExport\Model\Source\Import;

class EntityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ImportExport\Model\Source\Import\Entity
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_importConfigMock;

    protected function setUp()
    {
        $this->_importConfigMock = $this->getMock('Magento\ImportExport\Model\Import\ConfigInterface');
        $this->_model = new \Magento\ImportExport\Model\Source\Import\Entity($this->_importConfigMock);
    }

    public function testToOptionArray()
    {
        $entities = [
            'entity_name_1' => ['name' => 'entity_name_1', 'label' => 'entity_label_1'],
            'entity_name_2' => ['name' => 'entity_name_2', 'label' => 'entity_label_2'],
        ];
        $expectedResult = [
            ['label' => __('-- Please Select --'), 'value' => ''],
            ['label' => __('entity_label_1'), 'value' => 'entity_name_1'],
            ['label' => __('entity_label_2'), 'value' => 'entity_name_2'],
        ];
        $this->_importConfigMock->expects($this->any())->method('getEntities')->will($this->returnValue($entities));
        $this->assertEquals($expectedResult, $this->_model->toOptionArray());
    }
}
