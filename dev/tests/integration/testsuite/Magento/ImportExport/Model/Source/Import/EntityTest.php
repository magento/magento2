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
        $entities = array(
            'entity_name_1' => array('name' => 'entity_name_1', 'label' => 'entity_label_1'),
            'entity_name_2' => array('name' => 'entity_name_2', 'label' => 'entity_label_2')
        );
        $expectedResult = array(
            array('label' => __('-- Please Select --'), 'value' => ''),
            array('label' => __('entity_label_1'), 'value' => 'entity_name_1'),
            array('label' => __('entity_label_2'), 'value' => 'entity_name_2')
        );
        $this->_importConfigMock->expects($this->any())->method('getEntities')->will($this->returnValue($entities));
        $this->assertEquals($expectedResult, $this->_model->toOptionArray());
    }
}
