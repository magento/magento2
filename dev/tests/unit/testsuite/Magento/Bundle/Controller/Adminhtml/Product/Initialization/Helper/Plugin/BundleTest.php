<?php
/**
 * Test class for \Magento\Bundle\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Bundle
 *
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
namespace Magento\Bundle\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

class BundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Bundle
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', array(), array(), '', false);
        $methods = array(
            'getCompositeReadonly',
            'setBundleOptionsData',
            'setBundleSelectionsData',
            'getPriceType',
            'setCanSaveCustomOptions',
            'getProductOptions',
            'setProductOptions',
            'setCanSaveBundleSelections',
            '__wakeup'
        );
        $this->productMock = $this->getMock('\Magento\Catalog\Model\Product', $methods, array(), '', false);
        $this->subjectMock = $this->getMock(
            'Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper',
            array(),
            array(),
            '',
            false
        );
        $this->model = new \Magento\Bundle\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Bundle(
            $this->requestMock
        );
    }

    public function testAfterInitializeIfBundleAnsCustomOptionsAndBundleSelectionsExist()
    {
        $productOptionsBefore = array(0 => array('key' => 'value'), 1 => array('is_delete' => false));
        $productOptionsAfter = array(0 => array('key' => 'value', 'is_delete' => 1), 1 => array('is_delete' => 1));
        $postValue = 'postValue';
        $valueMap = array(
            array('bundle_options', null, $postValue),
            array('bundle_selections', null, $postValue),
            array('affect_bundle_product_selections', null, 1)
        );
        $this->requestMock->expects($this->any())->method('getPost')->will($this->returnValueMap($valueMap));
        $this->productMock->expects($this->any())->method('getCompositeReadonly')->will($this->returnValue(false));
        $this->productMock->expects($this->once())->method('setBundleOptionsData')->with($postValue);
        $this->productMock->expects($this->once())->method('setBundleSelectionsData')->with($postValue);
        $this->productMock->expects($this->once())->method('getPriceType')->will($this->returnValue(0));
        $this->productMock->expects($this->any())->method('getOptionsReadonly')->will($this->returnValue(false));
        $this->productMock->expects($this->once())->method('setCanSaveCustomOptions')->with(true);
        $this->productMock->expects(
            $this->once()
        )->method(
            'getProductOptions'
        )->will(
            $this->returnValue($productOptionsBefore)
        );
        $this->productMock->expects($this->once())->method('setProductOptions')->with($productOptionsAfter);
        $this->productMock->expects($this->once())->method('setCanSaveBundleSelections')->with(true);
        $this->model->afterInitialize($this->subjectMock, $this->productMock);
    }

    public function testAfterInitializeIfBundleSelectionsAndCustomOptionsExist()
    {
        $postValue = 'postValue';
        $valueMap = array(
            array('bundle_options', null, $postValue),
            array('bundle_selections', null, false),
            array('affect_bundle_product_selections', null, false)
        );
        $this->requestMock->expects($this->any())->method('getPost')->will($this->returnValueMap($valueMap));
        $this->productMock->expects($this->any())->method('getCompositeReadonly')->will($this->returnValue(false));
        $this->productMock->expects($this->once())->method('setBundleOptionsData')->with($postValue);
        $this->productMock->expects($this->never())->method('setBundleSelectionsData');
        $this->productMock->expects($this->once())->method('getPriceType')->will($this->returnValue(2));
        $this->productMock->expects($this->any())->method('getOptionsReadonly')->will($this->returnValue(true));
        $this->productMock->expects($this->once())->method('setCanSaveBundleSelections')->with(false);
        $this->model->afterInitialize($this->subjectMock, $this->productMock);
    }

    public function testAfterInitializeIfCustomAndBundleOptionNotExist()
    {
        $postValue = 'postValue';
        $valueMap = array(
            array('bundle_options', null, false),
            array('bundle_selections', null, $postValue),
            array('affect_bundle_product_selections', null, 1)
        );
        $this->requestMock->expects($this->any())->method('getPost')->will($this->returnValueMap($valueMap));
        $this->productMock->expects($this->any())->method('getCompositeReadonly')->will($this->returnValue(false));
        $this->productMock->expects($this->never())->method('setBundleOptionsData');
        $this->productMock->expects($this->once())->method('setBundleSelectionsData')->with($postValue);
        $this->productMock->expects($this->once())->method('getPriceType')->will($this->returnValue(0));
        $this->productMock->expects($this->any())->method('getOptionsReadonly')->will($this->returnValue(false));
        $this->productMock->expects($this->once())->method('setCanSaveCustomOptions')->with(true);
        $this->productMock->expects($this->once())->method('getProductOptions')->will($this->returnValue(false));
        $this->productMock->expects($this->never())->method('setProductOptions');
        $this->productMock->expects($this->once())->method('setCanSaveBundleSelections')->with(true);
        $this->model->afterInitialize($this->subjectMock, $this->productMock);
    }
}
