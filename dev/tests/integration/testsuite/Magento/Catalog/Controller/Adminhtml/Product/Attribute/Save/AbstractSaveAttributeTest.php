<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Attribute\Save;

use Magento\Catalog\Api\ProductAttributeOptionManagementInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\Store;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Base create and assert attribute data.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractSaveAttributeTest extends AbstractBackendController
{
    /** @var ProductAttributeRepositoryInterface */
    protected $productAttributeRepository;

    /** @var Escaper */
    protected $escaper;

    /** @var Json */
    protected $jsonSerializer;

    /** @var ProductAttributeOptionManagementInterface */
    protected $productAttributeOptionManagement;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->escaper = $this->_objectManager->get(Escaper::class);
        $this->jsonSerializer = $this->_objectManager->get(Json::class);
        $this->productAttributeOptionManagement = $this->_objectManager->get(
            ProductAttributeOptionManagementInterface::class
        );
        $this->productAttributeRepository = $this->_objectManager->get(ProductAttributeRepositoryInterface::class);
    }

    /**
     * Create attribute via save product attribute controller and assert that attribute
     * created correctly.
     *
     * @param array $attributeData
     * @param array $checkArray
     * @return void
     */
    protected function createAttributeUsingDataAndAssert(array $attributeData, array $checkArray): void
    {
        $attributeCode = $this->getAttributeCodeFromAttributeData($attributeData);
        if (isset($attributeData['serialized_options_arr'])) {
            $attributeData['serialized_options'] = $this->serializeOptions($attributeData['serialized_options_arr']);
        }
        $this->dispatchAttributeSave($attributeData);
        $this->assertSessionMessages(
            $this->equalTo([(string)__('You saved the product attribute.')]),
            MessageInterface::TYPE_SUCCESS
        );
        try {
            $attribute = $this->productAttributeRepository->get($attributeCode);
            $this->assertAttributeData($attribute, $attributeData, $checkArray);
            $this->productAttributeRepository->delete($attribute);
        } catch (NoSuchEntityException $e) {
            $this->fail("Attribute with code {$attributeCode} was not created.");
        }
    }

    /**
     * Create attribute via save product attribute controller and assert that we have error during save process.
     *
     * @param array $attributeData
     * @param string $errorMessage
     * @return void
     */
    protected function createAttributeUsingDataWithErrorAndAssert(array $attributeData, string $errorMessage): void
    {
        if (isset($attributeData['serialized_options_arr'])
            && count($attributeData['serialized_options_arr'])
        ) {
            $attributeData['serialized_options'] = $this->serializeOptions($attributeData['serialized_options_arr']);
        }
        $this->dispatchAttributeSave($attributeData);
        $this->assertSessionMessages(
            $this->equalTo([$this->escaper->escapeHtml($errorMessage)]),
            MessageInterface::TYPE_ERROR
        );
        $attributeCode = $this->getAttributeCodeFromAttributeData($attributeData);
        try {
            $attribute = $this->productAttributeRepository->get($attributeCode);
            $this->productAttributeRepository->delete($attribute);
        } catch (NoSuchEntityException $e) {
            //Attribute already deleted.
        }
    }

    /**
     * Assert that options was created.
     *
     * @param AttributeInterface $attribute
     * @param array $optionsData
     * @return void
     */
    protected function assertAttributeOptions(AttributeInterface $attribute, array $optionsData): void
    {
        $attributeOptions = $this->productAttributeOptionManagement->getItems($attribute->getAttributeCode());
        foreach ($optionsData as $optionData) {
            $valueItemArr = $optionData['option']['value'];
            $optionLabel = reset($valueItemArr)[1];
            $optionFounded = false;
            foreach ($attributeOptions as $attributeOption) {
                if ($attributeOption->getLabel() === $optionLabel) {
                    $optionFounded = true;
                    break;
                }
            }
            $this->assertTrue($optionFounded);
        }
    }

    /**
     * Compare attribute data with data which we use for create attribute.
     *
     * @param AttributeInterface|AbstractAttribute $attribute
     * @param array $attributeData
     * @param array $checkData
     * @return void
     */
    private function assertAttributeData(
        AttributeInterface $attribute,
        array $attributeData,
        array $checkData
    ): void {
        $frontendInput = $checkData['frontend_input'] ?? $attributeData['frontend_input'];
        $this->assertEquals('Test attribute name', $attribute->getDefaultFrontendLabel());
        $this->assertEquals($frontendInput, $attribute->getFrontendInput());

        if (isset($attributeData['serialized_options'])) {
            $this->assertAttributeOptions($attribute, $attributeData['serialized_options_arr']);
        }

        //Additional asserts
        foreach ($checkData as $valueKey => $value) {
            $this->assertEquals($value, $attribute->getDataUsingMethod($valueKey));
        }
    }

    /**
     * Get attribute code from attribute data. If attribute code doesn't exist in
     * attribute data get attribute using default frontend label.
     *
     * @param array $attributeData
     * @return string
     */
    private function getAttributeCodeFromAttributeData(array $attributeData): string
    {
        $attributeCode = $attributeData['attribute_code'] ?? null;
        if (!$attributeCode) {
            $attributeCode = strtolower(
                str_replace(' ', '_', $attributeData['frontend_label'][Store::DEFAULT_STORE_ID])
            );
        }

        return $attributeCode;
    }

    /**
     * Create attribute using catalog/product_attribute/save action.
     *
     * @param array $attributeData
     * @return void
     */
    private function dispatchAttributeSave(array $attributeData): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($attributeData);
        $this->dispatch('backend/catalog/product_attribute/save');
    }

    /**
     * Create serialized options string.
     *
     * @param array $optionsArr
     * @return string
     */
    private function serializeOptions(array $optionsArr): string
    {
        $resultArr = [];

        foreach ($optionsArr as $option) {
            $resultArr[] = http_build_query($option);
        }

        return $this->jsonSerializer->serialize($resultArr);
    }
}
