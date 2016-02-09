<?php
/**
 * Test class for \Magento\Bundle\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Bundle
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

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
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $methods = [
            'getCompositeReadonly',
            'setBundleOptionsData',
            'setBundleSelectionsData',
            'getPriceType',
            'setCanSaveCustomOptions',
            'getProductOptions',
            'setOptions',
            'setCanSaveBundleSelections',
            '__wakeup',
        ];
        $this->productMock = $this->getMock('\Magento\Catalog\Model\Product', $methods, [], '', false);
        $optionInterfaceFactory = $this->getMockBuilder('Magento\Bundle\Api\Data\OptionInterfaceFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $linkInterfaceFactory = $this->getMockBuilder('Magento\Bundle\Api\Data\LinkInterfaceFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $productRepository = $this->getMockBuilder('Magento\Catalog\Api\ProductRepositoryInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customOptionFactory = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->subjectMock = $this->getMock(
            'Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper',
            [],
            [],
            '',
            false
        );
        $this->model = new \Magento\Bundle\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Bundle(
            $this->requestMock,
            $optionInterfaceFactory,
            $linkInterfaceFactory,
            $productRepository,
            $storeManager,
            $customOptionFactory
        );
    }

    public function testAfterInitializeIfBundleAnsCustomOptionsAndBundleSelectionsExist()
    {
        $this->markTestSkipped(
            'Tested class under development'
        );
        $productOptionsBefore = [0 => ['key' => 'value'], 1 => ['is_delete' => false]];
        $postValue = 'postValue';
        $valueMap = [
            ['bundle_options', null, $postValue],
            ['bundle_selections', null, $postValue],
            ['affect_bundle_product_selections', null, 1],
        ];
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
        $this->productMock->expects($this->once())->method('setOptions')->with(null);
        $this->productMock->expects($this->once())->method('setCanSaveBundleSelections')->with(true);
        $this->model->afterInitialize($this->subjectMock, $this->productMock);
    }

    public function testAfterInitializeIfBundleSelectionsAndCustomOptionsExist()
    {
        $this->markTestSkipped(
            'Tested class under development'
        );
        $postValue = 'postValue';
        $valueMap = [
            ['bundle_options', null, $postValue],
            ['bundle_selections', null, false],
            ['affect_bundle_product_selections', null, false],
        ];
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
        $this->markTestSkipped(
            'Tested class under development'
        );
        $postValue = 'postValue';
        $valueMap = [
            ['bundle_options', null, false],
            ['bundle_selections', null, $postValue],
            ['affect_bundle_product_selections', null, 1],
        ];
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
