<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model\Rule\Condition;

use \Magento\Rule\Model\Condition\Context;
use \Magento\Backend\Helper\Data;
use \Magento\Eav\Model\Config;
use \Magento\Catalog\Model\ProductFactory;
use \Magento\Catalog\Api\ProductRepositoryInterface;
use \Magento\Eav\Model\Entity\AbstractEntity;
use \Magento\Catalog\Model\ResourceModel\Product;
use \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use \Magento\Framework\Locale\FormatInterface;
use \Magento\Eav\Model\Entity\AttributeLoaderInterface;
use \Magento\SalesRule\Model\Rule\Condition\Product as SalesRuleProduct;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductTest extends \PHPUnit\Framework\TestCase
{
    /** @var SalesRuleProduct */
    protected $model;

    /** @var Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $backendHelperMock;

    /** @var Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $configMock;

    /** @var ProductFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $productFactoryMock;

    /** @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $productRepositoryMock;

    /** @var Product|\PHPUnit_Framework_MockObject_MockObject */
    protected $productMock;

    /** @var Collection|\PHPUnit_Framework_MockObject_MockObject */
    protected $collectionMock;

    /** @var FormatInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $formatMock;

    /** @var AttributeLoaderInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeLoaderInterfaceMock;

    /**
     * Setup the test
     */
    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendHelperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productFactoryMock = $this->getMockBuilder(ProductFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRepositoryMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->attributeLoaderInterfaceMock = $this->getMockBuilder(AbstractEntity::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributesByCode'])
            ->getMock();
        $this->attributeLoaderInterfaceMock
            ->expects($this->any())
            ->method('getAttributesByCode')
            ->will($this->returnValue([]));
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadAllAttributes'])
            ->getMock();
        $this->productMock
            ->expects($this->any())
            ->method('loadAllAttributes')
            ->will($this->returnValue($this->attributeLoaderInterfaceMock));
        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->formatMock = $this->getMockBuilder(FormatInterface::class)
            ->getMockForAbstractClass();
        $this->model = new SalesRuleProduct(
            $this->contextMock,
            $this->backendHelperMock,
            $this->configMock,
            $this->productFactoryMock,
            $this->productRepositoryMock,
            $this->productMock,
            $this->collectionMock,
            $this->formatMock
        );
    }

    /**
     * @return array
     */
    public function getValueElementChooserUrlDataProvider()
    {
        return [
            'category_ids_without_js_object' => [
                'category_ids',
                'sales_rule/promo_widget/chooser/attribute/'
            ],
            'category_ids_with_js_object' => [
                'category_ids',
                'sales_rule/promo_widget/chooser/attribute/',
                'jsobject'
            ],
            'sku_without_js_object' => [
                'sku',
                'sales_rule/promo_widget/chooser/attribute/',
                'jsobject'
            ],
            'sku_without_with_js_object' => [
                'sku',
                'sales_rule/promo_widget/chooser/attribute/'
            ],
            'none' => [
                '',
                ''
            ]
        ];
    }

    /**
     * test getValueElementChooserUrl
     * @param string $attribute
     * @param string $url
     * @param string $jsObject
     * @dataProvider getValueElementChooserUrlDataProvider
     */
    public function testGetValueElementChooserUrl($attribute, $url, $jsObject = '')
    {
        $this->model->setJsFormObject($jsObject);
        $this->model->setAttribute($attribute);
        $url .= $this->model->getAttribute();
        $this->backendHelperMock
            ->expects($this->any())
            ->method('getUrl')
            ->willReturnArgument(0);

        if ($this->model->getJsFormObject()) {
            $url .= '/form/' . $this->model->getJsFormObject();
        }

        $this->assertEquals($url, $this->model->getValueElementChooserUrl());
    }
}
