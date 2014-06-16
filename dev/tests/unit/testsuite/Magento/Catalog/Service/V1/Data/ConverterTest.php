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
namespace Magento\Catalog\Service\V1\Data;

use Magento\Catalog\Service\V1\Data\ProductBuilder;
use Magento\Catalog\Service\V1\Data\Product as ProductDataObject;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductBuilder
     */
    protected $productBuilder;

    /**
     * @var Converter | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $converter;

    protected function setUp()
    {
        $this->productBuilder = $this->getMock(
            'Magento\Catalog\Service\V1\Data\ProductBuilder',
            [],
            [],
            '',
            false
        );
    }

    public function testCreateProductDataFromModel()
    {
        $productModelMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();
        $attrCodes = ['sku', 'price', 'status', 'updatedAt', 'entity_id'];
        $this->productBuilder->expects($this->once())
            ->method('getCustomAttributesCodes')
            ->will($this->returnValue($attrCodes));

        $attributes = [
            ProductDataObject::SKU => ProductDataObject::SKU . 'value',
            ProductDataObject::PRICE => ProductDataObject::PRICE . 'value',
            ProductDataObject::STATUS => ProductDataObject::STATUS . 'dataValue',
            ProductDataObject::ID => 'entity_id' . 'value'
        ];
        $this->productBuilder->expects($this->once())
            ->method('populateWithArray')
            ->with($attributes);

        $this->productBuilder->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($attributes));

        $this->productBuilder->expects($this->once())
            ->method('create')
            ->will($this->returnValue(new ProductDataObject($this->productBuilder)));

        $dataUsingMethodCallback = $this->returnCallback(
            function ($attrCode) {
                if (in_array($attrCode, ['sku', 'price', 'entity_id'])) {
                    return $attrCode . 'value';
                }
                return null;
            }
        );
        $productModelMock->expects($this->exactly(count($attrCodes)))
            ->method('getDataUsingMethod')
            ->will($dataUsingMethodCallback);

        $dataCallback = $this->returnCallback(
            function ($attrCode) {
                if ($attrCode == 'status') {
                    return $attrCode . 'dataValue';
                }
                return null;
            }
        );
        $productModelMock->expects($this->exactly(2))
            ->method('getData')
            ->will($dataCallback);

        $this->converter = new Converter($this->productBuilder);
        $productData = $this->converter->createProductDataFromModel($productModelMock);
        $this->assertEquals(ProductDataObject::SKU . 'value', $productData->getSku());
        $this->assertEquals('entity_id' . 'value', $productData->getId());
        $this->assertEquals(ProductDataObject::PRICE . 'value', $productData->getPrice());
        $this->assertEquals(ProductDataObject::STATUS . 'dataValue', $productData->getStatus());
        $this->assertEquals(null, $productData->getUpdatedAt());
    }
}
