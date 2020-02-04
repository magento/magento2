<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Controller\Adminhtml\Product\Attribute\Save\InputType;

use Magento\Catalog\Controller\Adminhtml\Product\Attribute\Save\InputType\AbstractSaveAttributeTest;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Table;

/**
 * Test cases related to create attribute with input type text swatch.
 *
 * @magentoDbIsolation enabled
 */
class TextSwatchTest extends AbstractSaveAttributeTest
{
    /**
     * Test create attribute and compare attribute data and input data.
     *
     * @dataProvider \Magento\TestFramework\Swatches\Model\Attribute\DataProvider\TextSwatch::getAttributeDataWithCheckArray()
     *
     * @param array $attributePostData
     * @param array $checkArray
     * @return void
     */
    public function testCreateAttribute(array $attributePostData, array $checkArray): void
    {
        $this->createAttributeUsingDataAndAssert($attributePostData, $checkArray);
    }

    /**
     * Test create attribute with error.
     *
     * @dataProvider \Magento\TestFramework\Swatches\Model\Attribute\DataProvider\TextSwatch::getAttributeDataWithErrorMessage()
     *
     * @param array $attributePostData
     * @param string $errorMessage
     * @return void
     */
    public function testCreateAttributeWithError(array $attributePostData, string $errorMessage): void
    {
        $this->createAttributeUsingDataWithErrorAndAssert($attributePostData, $errorMessage);
    }

    /**
     * @inheritdoc
     */
    protected function assertAttributeOptions(AttributeInterface $attribute, array $optionsData): void
    {
        /** @var Table $attributeSource */
        $attributeSource = $attribute->getSource();
        $swatchOptions = $attributeSource->getAllOptions(true, true);
        foreach ($optionsData as $optionData) {
            $optionVisualValueArr = $optionData['optiontext']['value'];
            $optionVisualValue = reset($optionVisualValueArr)[0];
            $optionFounded = false;
            foreach ($swatchOptions as $attributeOption) {
                if ($attributeOption['label'] === $optionVisualValue) {
                    $optionFounded = true;
                    break;
                }
            }
            $this->assertTrue($optionFounded);
        }
    }
}
