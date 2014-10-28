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
namespace Magento\ConfigurableProduct\Model;

class SuggestedAttributeListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Model\SuggestedAttributeList
     */
    protected $suggestedListModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeMock;

    /**
     * @var string
     */
    protected $labelPart = 'labelPart';

    protected function setUp()
    {
        $this->attributeFactoryMock = $this->getMock(
            'Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory',
            array('create')
        );
        $this->resourceHelperMock = $this->getMock(
            'Magento\Catalog\Model\Resource\Helper',
            array(),
            array(),
            '',
            false
        );
        $this->collectionMock = $this->getMock(
            'Magento\Catalog\Model\Resource\Product\Attribute\Collection',
            array(),
            array(),
            '',
            false
        );
        $this->resourceHelperMock->expects(
            $this->once()
        )->method(
            'addLikeEscape'
        )->with(
            $this->labelPart,
            array('position' => 'any')
        )->will(
            $this->returnValue($this->labelPart)
        );
        $this->attributeFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->collectionMock)
        );
        $valueMap = array(
            array('frontend_input', 'select', $this->collectionMock),
            array('frontend_label', array('like' => $this->labelPart), $this->collectionMock),
            array('is_configurable', array(array('eq' => 1), array('null' => true)), $this->collectionMock),
            array('is_user_defined', 1, $this->collectionMock),
            array('is_global', \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_GLOBAL, $this->collectionMock)
        );
        $this->collectionMock->expects(
            $this->any()
        )->method(
            'addFieldToFilter'
        )->will(
            $this->returnValueMap($valueMap)
        );
        $methods = array('getId', 'getFrontendLabel', 'getAttributeCode', 'getSource', '__wakeup', 'getApplyTo');
        $this->attributeMock = $this->getMock(
            'Magento\Catalog\Model\Resource\Eav\Attribute',
            $methods,
            array(),
            '',
            false
        );
        $this->collectionMock->expects(
            $this->once()
        )->method(
            'getItems'
        )->will(
            $this->returnValue(array('id' => $this->attributeMock))
        );
        $this->suggestedListModel = new \Magento\ConfigurableProduct\Model\SuggestedAttributeList(
            $this->attributeFactoryMock,
            $this->resourceHelperMock
        );
    }

    public function testGetSuggestedAttributesIfTheyApplicable()
    {
        $source = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\Source\AbstractSource',
            array(),
            array(),
            '',
            false
        );
        $result['id'] = array('id' => 'id', 'label' => 'label', 'code' => 'code', 'options' => 'options');
        $this->attributeMock->expects($this->any())->method('getApplyTo')->will($this->returnValue(false));
        $this->attributeMock->expects($this->once())->method('getId')->will($this->returnValue('id'));
        $this->attributeMock->expects($this->once())->method('getFrontendLabel')->will($this->returnValue('label'));
        $this->attributeMock->expects($this->once())->method('getAttributeCode')->will($this->returnValue('code'));
        $this->attributeMock->expects($this->once())->method('getSource')->will($this->returnValue($source));
        $source->expects($this->once())->method('getAllOptions')->with(false)->will($this->returnValue('options'));
        $this->assertEquals($result, $this->suggestedListModel->getSuggestedAttributes($this->labelPart));
    }

    public function testGetSuggestedAttributesIfTheyNotApplicable()
    {
        $this->attributeMock->expects($this->any())->method('getApplyTo')->will($this->returnValue(array('simple')));
        $this->attributeMock->expects($this->never())->method('getId');
        $this->attributeMock->expects($this->never())->method('getFrontendLabel');
        $this->attributeMock->expects($this->never())->method('getAttributeCode');
        $this->attributeMock->expects($this->never())->method('getSource');
        $this->assertEquals(array(), $this->suggestedListModel->getSuggestedAttributes($this->labelPart));
    }
}
