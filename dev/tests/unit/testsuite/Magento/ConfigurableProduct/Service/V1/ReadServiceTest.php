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
namespace Magento\ConfigurableProduct\Service\V1;

use Magento\Catalog\Service\V1\Data\Product;
use Magento\Catalog\Service\V1\Data\ProductBuilder;
use Magento\Catalog\Service\V1\Product\Attribute\ReadServiceInterface;
use Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix;
use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class ReadServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var ReadService */
    protected $object;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var ReadServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeReadService;

    /**
     * @var VariationMatrix|\PHPUnit_Framework_MockObject_MockObject
     */
    private $variationMatrix;

    /**
     * @var ProductBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productBuilder;

    protected function setUp()
    {
        $this->attributeReadService = $this->getMockBuilder(
            'Magento\Catalog\Service\V1\Product\Attribute\ReadServiceInterface'
        )->disableOriginalConstructor()->getMock();

        $this->variationMatrix = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix'
        )->disableOriginalConstructor()->getMock();

        $this->productBuilder = $this->getMockBuilder(
            'Magento\Catalog\Service\V1\Data\ProductBuilder'
        )->disableOriginalConstructor()->getMock();


        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->object = $this->objectManagerHelper->getObject(
            'Magento\ConfigurableProduct\Service\V1\ReadService',
            [
                'attributeReadService' => $this->attributeReadService,
                'variationMatrix' => $this->variationMatrix,
                'productBuilder' => $this->productBuilder,
            ]
        );
    }

    /**
     * @param array $configurableAttributeData
     * @dataProvider productVariationDataProvider
     */
    public function testGenerateVariation($configurableAttributeData)
    {
        $attributeCode = 'code';
        $attribute = $this->getMockBuilder('Magento\Catalog\Service\V1\Data\Eav\AttributeMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $attribute->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue($attributeCode));


        $this->attributeReadService->expects($this->once())
            ->method('info')
            ->with($configurableAttributeData['attribute_id'])
            ->will($this->returnValue($attribute));

        $options = null;
        $this->variationMatrix->expects($this->any())
            ->method('getVariations')
            ->with(
                [
                    $configurableAttributeData['attribute_id'] => [
                        "attribute_id" => $configurableAttributeData['attribute_id'],
                        "values" => $configurableAttributeData['values'],
                        "options" => $options,
                        "attribute_code" => $attributeCode,
                    ]
                ]
            )
            ->will(
                $this->returnValue(
                    [
                        [
                            $configurableAttributeData['attribute_id'] => [
                                'value' => '14',
                                'label' => 'dd',
                                'price' => [
                                    'index' => 14,
                                    'price' => 10,
                                ],
                            ],
                        ],
                    ]
                )
            );

        $product = $this->getMockBuilder('Magento\Catalog\Service\V1\Data\Product')
            ->disableOriginalConstructor()
            ->getMock();
        $product->expects($this->any())
            ->method('getPrice')
            ->will($this->returnValue(100));

        $configurableAttribute = $this->getMockBuilder(
            'Magento\ConfigurableProduct\Service\V1\Data\Option'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $configurableAttribute->expects($this->any())
            ->method('__toArray')
            ->will($this->returnValue($configurableAttributeData));
        $configurableAttribute->expects($this->any())
            ->method('getAttributeId')
            ->will($this->returnValue($configurableAttributeData['attribute_id']));

        $this->productBuilder->expects($this->any())
            ->method('populute')
            ->with($product);

        $this->productBuilder->expects($this->once())
            ->method('setCustomAttribute')
            ->with($attributeCode, 14);
        $this->productBuilder->expects($this->once())
            ->method('setPrice')
            ->with(110);

        $this->productBuilder->expects($this->once())
            ->method('create')
            ->will($this->returnValue($product));

        $result = $this->object->generateVariation($product, [$configurableAttribute]);
        $this->assertCount(1, $result);
        $this->assertEquals([$product], $result);

    }

    /**
     * @return array
     */
    public function productVariationDataProvider()
    {
        return [
            [
                [
                    "attribute_id" => 174,
                    "values" => [
                        [
                            "index" => 14,
                            "price" => 100.0
                        ]
                    ]
                ]
            ]
        ];
    }
}
