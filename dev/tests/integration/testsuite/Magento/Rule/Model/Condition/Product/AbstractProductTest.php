<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rule\Model\Condition\Product;

use Magento\Backend\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductCategoryList;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Magento\Framework\Locale\FormatInterface;
use Magento\Rule\Model\Condition\Context;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Provide tests for Abstract Rule product condition data model.
 * @magentoAppArea adminhtml
 */
class AbstractProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test subject.
     *
     * @var AbstractProduct|\PHPUnit_Framework_MockObject_MockObject
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $context = $objectManager->get(Context::class);
        $helperData = $objectManager->get(Data::class);
        $config = $objectManager->get(Config::class);
        $productFactory = $objectManager->get(ProductFactory::class);
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $productResource = $objectManager->get(Product::class);
        $attributeSetCollection = $objectManager->get(Collection::class);
        $localeFormat = $objectManager->get(FormatInterface::class);
        $data = [];
        $productCategoryList = $objectManager->get(ProductCategoryList::class);
        $this->model = $this->getMockBuilder(AbstractProduct::class)
            ->setMethods(['getOperator', 'getFormName', 'setFormName'])
            ->setConstructorArgs([
                $context,
                $helperData,
                $config,
                $productFactory,
                $productRepository,
                $productResource,
                $attributeSetCollection,
                $localeFormat,
                $data,
                $productCategoryList
            ])
            ->getMockForAbstractClass();
    }

    /**
     * Test Abstract Rule product condition data model shows attribute labels in more readable view
     * (without html tags, if one presented).
     *
     * @magentoDataFixture Magento/Rule/_files/dropdown_attribute_with_html.php
     */
    public function testGetValueSelectOptions()
    {
        $expectedLabels = [' ', 'Option 1', 'Option 2', 'Option 3'];
        $this->model->setAttribute('dropdown_attribute_with_html');
        $options = $this->model->getValueSelectOptions();
        $labels = [];
        foreach ($options as $option) {
            $labels[] = $option['label'];
        }
        self::assertSame($expectedLabels, $labels);
    }
}
