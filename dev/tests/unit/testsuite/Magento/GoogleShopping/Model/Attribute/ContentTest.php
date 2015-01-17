<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Model\Attribute;

class ContentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider convertAttributeDataProvider
     * @param int|null $attributeId
     * @param string $description
     * @param string $mapValue
     */
    public function testConvertAttribute($attributeId, $description, $mapValue)
    {
        $product = $this->getMock(
            '\Magento\Catalog\Model\Product',
            ['getDescription', '__wakeup'],
            [],
            '',
            false
        );
        $product->expects($this->any())->method('getDescription')->will($this->returnValue($description));

        $defaultFrontend = $this->getMock(
            'Magento\Eav\Model\Entity\Attribute\Frontend\DefaultFrontend',
            ['getValue'],
            [],
            '',
            false
        );
        $defaultFrontend->expects($this->any())
            ->method('getValue')
            ->with($product)
            ->will($this->returnValue($mapValue));

        $attribute = $this->getMock(
            '\Magento\Catalog\Model\Entity\Attribute',
            ['getFrontend', '__wakeup'],
            [],
            '',
            false
        );
        $attribute->expects($this->any())->method('getFrontend')->will($this->returnValue($defaultFrontend));

        $productHelper = $this->getMock(
            '\Magento\GoogleShopping\Helper\Product',
            ['getProductAttribute'],
            [],
            '',
            false
        );
        $productHelper->expects($this->any())
            ->method('getProductAttribute')
            ->with($product, $attributeId)
            ->will($this->returnValue($attribute));

        $googleShoppingHelper = $this->getMock(
            '\Magento\GoogleShopping\Helper\Data',
            ['cleanAtomAttribute'],
            [],
            '',
            false
        );
        $googleShoppingHelper->expects($this->once())
            ->method('cleanAtomAttribute')
            ->with($mapValue)
            ->will($this->returnValue($mapValue));

        $model = (new \Magento\TestFramework\Helper\ObjectManager($this))
            ->getObject(
                '\Magento\GoogleShopping\Model\Attribute\Content',
                ['gsProduct' => $productHelper, 'googleShoppingHelper' => $googleShoppingHelper]
            );

        $service = $this->getMock('Zend_Gdata_App', ['newContent', 'setText'], [], '', false);
        $service->expects($this->once())->method('newContent')->will($this->returnSelf());
        $service->expects($this->once())->method('setText')->with($mapValue)->will($this->returnValue($mapValue));

        $entry = $this->getMock(
            '\Magento\Framework\Gdata\Gshopping\Entry',
            ['getService', 'setContent'],
            [],
            '',
            false
        );
        $entry->expects($this->once())->method('getService')->will($this->returnValue($service));
        $entry->expects($this->once())->method('setContent')->with($mapValue);

        $groupAttributeDescription = $this->getMock(
            '\Magento\GoogleShopping\Model\Attribute\DefaultAttribute',
            [],
            [],
            '',
            false
        );

        $model->setGroupAttributeDescription($groupAttributeDescription);
        $model->setAttributeId($attributeId);

        $this->assertEquals($entry, $model->convertAttribute($product, $entry));
    }

    /**
     * @return array
     */
    public function convertAttributeDataProvider()
    {
        return [
            [1, 'description', 'short description'],
            [null, 'description', 'description'],
        ];
    }
}
