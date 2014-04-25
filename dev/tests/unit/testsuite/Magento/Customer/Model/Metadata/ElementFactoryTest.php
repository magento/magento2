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
namespace Magento\Customer\Model\Metadata;

class ElementFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\ObjectManager | \PHPUnit_Framework_MockObject_MockObject */
    private $_objectManager;

    /** @var \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata | \PHPUnit_Framework_MockObject_MockObject */
    private $_attributeMetadata;

    /** @var string */
    private $_entityTypeCode = 'customer_address';

    /** @var ElementFactory */
    private $_elementFactory;

    public function setUp()
    {
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManager', array(), array(), '', false);
        $this->_attributeMetadata = $this->getMock(
            'Magento\Customer\Service\V1\Data\Eav\AttributeMetadata',
            array(),
            array(),
            '',
            false
        );
        $this->_elementFactory = new ElementFactory($this->_objectManager, new \Magento\Framework\Stdlib\String());
    }

    /** TODO fix when Validation is implemented MAGETWO-17341 */
    public function testAttributePostcodeDataModelClass()
    {
        $this->_attributeMetadata->expects(
            $this->once()
        )->method(
            'getDataModel'
        )->will(
            $this->returnValue('Magento\Customer\Model\Attribute\Data\Postcode')
        );

        $dataModel = $this->getMock('Magento\Customer\Model\Metadata\Form\Text', array(), array(), '', false);
        $this->_objectManager->expects($this->once())->method('create')->will($this->returnValue($dataModel));

        $actual = $this->_elementFactory->create($this->_attributeMetadata, '95131', $this->_entityTypeCode);
        $this->assertSame($dataModel, $actual);
    }

    public function testAttributeEmptyDataModelClass()
    {
        $this->_attributeMetadata->expects($this->once())->method('getDataModel')->will($this->returnValue(''));
        $this->_attributeMetadata->expects(
            $this->once()
        )->method(
            'getFrontendInput'
        )->will(
            $this->returnValue('text')
        );

        $dataModel = $this->getMock('Magento\Customer\Model\Metadata\Form\Text', array(), array(), '', false);
        $params = array(
            'entityTypeCode' => $this->_entityTypeCode,
            'value' => 'Some Text',
            'isAjax' => false,
            'attribute' => $this->_attributeMetadata
        );
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Customer\Model\Metadata\Form\Text',
            $params
        )->will(
            $this->returnValue($dataModel)
        );

        $actual = $this->_elementFactory->create($this->_attributeMetadata, 'Some Text', $this->_entityTypeCode);
        $this->assertSame($dataModel, $actual);
    }
}
