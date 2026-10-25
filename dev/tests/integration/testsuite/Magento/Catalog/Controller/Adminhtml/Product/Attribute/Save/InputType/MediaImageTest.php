<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Attribute\Save\InputType;

use Magento\Catalog\Controller\Adminhtml\Product\Attribute\Save\AbstractSaveAttributeTest;
use Magento\TestFramework\Catalog\Model\Product\Attribute\DataProvider\MediaImage;
use PHPUnit\Framework\Attributes\DataProviderExternal;

/**
 * Test cases related to create attribute with input type media image.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 */
class MediaImageTest extends AbstractSaveAttributeTest
{
    /**
     * Test create attribute and compare attribute data and input data.
     *
     * @param array $attributePostData
     * @param array $checkArray
     * @return void
     */
    #[DataProviderExternal(MediaImage::class, 'getAttributeDataWithCheckArray')]
    public function testCreateAttribute(array $attributePostData, array $checkArray): void
    {
        $this->createAttributeUsingDataAndAssert($attributePostData, $checkArray);
    }

    /**
     * Test create attribute with error.
     *
     * @param array $attributePostData
     * @param string $errorMessage
     * @return void
     */
    #[DataProviderExternal(MediaImage::class, 'getAttributeDataWithErrorMessage')]
    public function testCreateAttributeWithError(array $attributePostData, string $errorMessage): void
    {
        $this->createAttributeUsingDataWithErrorAndAssert($attributePostData, $errorMessage);
    }
}
