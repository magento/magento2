<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Attribute\Update;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Attribute as AttributeResource;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option as OptionResource;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as OptionCollectionFactory;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Escaper;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Eav\Model\GetAttributeGroupByName;
use Magento\TestFramework\Eav\Model\GetAttributeSetByName;
use Magento\TestFramework\Eav\Model\ResourceModel\GetEntityIdByAttributeId;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Base update and assert attribute data.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractUpdateAttributeTest extends AbstractBackendController
{
    /** @var ProductAttributeRepositoryInterface */
    protected $productAttributeRepository;

    /** @var Escaper */
    protected $escaper;

    /** @var Json */
    protected $jsonSerializer;

    /** @var GetAttributeSetByName */
    private $getAttributeSetByName;

    /** @var GetAttributeGroupByName */
    private $getAttributeGroupByName;

    /** @var GetEntityIdByAttributeId */
    private $getEntityIdByAttributeId;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var OptionCollectionFactory */
    private $optionCollectionFactory;

    /** @var OptionResource */
    private $attributeOptionResource;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->escaper = $this->_objectManager->get(Escaper::class);
        $this->jsonSerializer = $this->_objectManager->get(Json::class);
        $this->productAttributeRepository = $this->_objectManager->get(ProductAttributeRepositoryInterface::class);
        $this->getAttributeSetByName = $this->_objectManager->get(GetAttributeSetByName::class);
        $this->getAttributeGroupByName = $this->_objectManager->get(GetAttributeGroupByName::class);
        $this->getEntityIdByAttributeId = $this->_objectManager->get(GetEntityIdByAttributeId::class);
        $this->storeManager = $this->_objectManager->get(StoreManagerInterface::class);
        $this->optionCollectionFactory = $this->_objectManager->get(OptionCollectionFactory::class);
        $this->attributeOptionResource = $this->_objectManager->get(OptionResource::class);
    }

    /**
     * Updates attribute frontend labels on stores for a given attribute type.
     *
     * @param string $attributeCode
     * @param array $postData
     * @param array $expectedData
     * @return void
     */
    protected function processUpdateFrontendLabelOnStores(
        string $attributeCode,
        array $postData,
        array $expectedData
    ): void {
        $this->setAttributeStorelabels($attributeCode);
        if (is_array($postData['frontend_label'])) {
            $postData['frontend_label'] = $this->prepareStoresData($postData['frontend_label']);
        }
        $expectedData['store_labels'] = $this->prepareStoresData($expectedData['store_labels']);

        $this->_objectManager->removeSharedInstance(AttributeResource::class);
        $this->updateAttributeUsingData($attributeCode, $postData);
        $this->assertUpdateAttributeProcess($attributeCode, $postData, $expectedData);
    }

    /**
     * Updates attribute options on stores for a given attribute type.
     *
     * @param string $attributeCode
     * @param array $postData
     * @return void
     */
    protected function processUpdateOptionsOnStores(string $attributeCode, array $postData): void
    {
        $optionsData = $this->prepareStoreOptionsArray($attributeCode, $postData['options_array']);
        $optionsPostData = $this->prepareStoreOptionsPostData($optionsData);
        $postData['serialized_options'] = $this->serializeOptions($optionsPostData);
        $expectedData = $this->prepareStoreOptionsExpectedData($optionsData);

        $this->_objectManager->removeSharedInstance(AttributeResource::class);
        $this->updateAttributeUsingData($attributeCode, $postData);
        $this->assertUpdateAttributeProcess($attributeCode, $postData, $expectedData);
    }

    /**
     * Prepare an array of values by store - replace store code with store identifier.
     *
     * @param array $storesData
     * @return array
     */
    protected function prepareStoresData(array $storesData): array
    {
        $storeIdsData = [];
        foreach ($storesData as $storeId => $label) {
            $store = $this->storeManager->getStore($storeId);
            $storeIdsData[$store->getId()] = $label;
        }

        return $storeIdsData;
    }

    /**
     * Update attribute via save product attribute controller.
     *
     * @param string $attributeCode
     * @param array $postData
     * @return void
     */
    protected function updateAttributeUsingData(string $attributeCode, array $postData): void
    {
        $attributeId = $postData['attribute_id'] ?? $this->productAttributeRepository->get($attributeCode)->getId();
        $this->dispatchAttributeSave($postData, (int)$attributeId);
    }

    /**
     * Replace the store code with an identifier in the array of option values
     *
     * @param array $optionsArray
     * @return array
     */
    protected function replaceStoreCodeWithId(array $optionsArray): array
    {
        foreach ($optionsArray as $key => $option) {
            $optionsArray[$key]['value'] = $this->prepareStoresData($option['value']);
        }

        return $optionsArray;
    }

    /**
     * Prepare an array of attribute option values that will be saved.
     *
     * @param string $attributeCode
     * @param array $optionsArray
     * @return array
     */
    protected function prepareStoreOptionsArray(string $attributeCode, array $optionsArray): array
    {
        $attribute = $this->productAttributeRepository->get($attributeCode);
        $replacedOptionsArray = $this->replaceStoreCodeWithId($optionsArray);
        $actualOptionsData = $this->getActualOptionsData($attribute->getId());
        $labeledOptionsData = [];
        $optionLabelIds = [];
        $i = 1;
        foreach ($actualOptionsData as $optionId => $optionData) {
            $optionLabelIds['option_' . $i] = $optionId;
            $labeledOptionsData['option_' . $i] = $optionData;
            $i++;
        }

        $combineOptionsData = array_replace_recursive($labeledOptionsData, $replacedOptionsArray);
        $optionsData = [];
        foreach ($optionLabelIds as $optionLabel => $optionId) {
            $optionsData[$optionId] = $combineOptionsData[$optionLabel];
        }

        return $optionsData;
    }

    /**
     * Get actual attribute options data.
     *
     * @param string $attributeId
     * @return array
     */
    protected function getActualOptionsData(string $attributeId): array
    {
        $attributeOptions = $this->getAttributeOptions($attributeId);
        $actualOptionsData = [];
        foreach ($attributeOptions as $optionId => $option) {
            $actualOptionsData[$optionId] = [
                'order' => $option->getSortOrder(),
                'value' => $this->getAttributeOptionValues($optionId),
            ];
        }

        return $actualOptionsData;
    }

    /**
     * Prepare an array of attribute option values for sending via post parameters.
     *
     * @param array $optionsData
     * @return array
     */
    protected function prepareStoreOptionsPostData(array $optionsData): array
    {
        $optionsPostData = [];
        foreach ($optionsData as $optionId => $option) {
            $optionsPostData[$optionId]['option'] = [
                'order' => [
                    $optionId => $option['order'],
                ],
                'value' => [
                    $optionId => $option['value'],
                ],
                'delete' => [
                    $optionId => $option['delete'] ?? '',
                ],
            ];
            if (isset($option['default'])) {
                $optionsPostData[$optionId]['default'][] = $optionId;
            }
        }

        return $optionsPostData;
    }

    /**
     * Prepare an array of attribute option values for verification after saving the attribute.
     *
     * @param array $optionsData
     * @return array
     */
    protected function prepareStoreOptionsExpectedData(array $optionsData): array
    {
        $optionsArray = [];
        $defaultValue = '';

        foreach ($optionsData as $optionId => $option) {
            if (!empty($option['delete'])) {
                continue;
            }
            $optionsArray[$optionId] = [
                'order' => $option['order'],
                'value' => $option['value'],
            ];
            if (isset($option['default'])) {
                $defaultValue = $optionId;
            }
        }

        return [
            'options_array' => $optionsArray,
            'default_value' => $defaultValue,
        ];
    }

    /**
     * Assert that attribute update correctly.
     *
     * @param string $attributeCode
     * @param array $postData
     * @param array $expectedData
     * @return void
     */
    protected function assertUpdateAttributeProcess(string $attributeCode, array $postData, array $expectedData): void
    {
        $this->assertSessionMessages(
            $this->equalTo([(string)__('You saved the product attribute.')]),
            MessageInterface::TYPE_SUCCESS
        );
        $updatedAttribute = $this->productAttributeRepository->get($attributeCode);
        if (isset($postData['new_attribute_set_name'])) {
            $this->assertUpdateAttributeSet($updatedAttribute, $postData);
        } elseif (isset($postData['options_array'])) {
            $this->assertUpdateAttributeOptions($updatedAttribute, $expectedData['options_array']);
            unset($expectedData['options_array']);
            $this->assertUpdateAttributeData($updatedAttribute, $expectedData);
        } else {
            $this->assertUpdateAttributeData($updatedAttribute, $expectedData);
        }
    }

    /**
     * Check that attribute property values match expected values.
     *
     * @param ProductAttributeInterface $attribute
     * @param array $expectedData
     * @return void
     */
    protected function assertUpdateAttributeData(
        ProductAttributeInterface $attribute,
        array $expectedData
    ): void {
        foreach ($expectedData as $key => $expectedValue) {
            $this->assertEquals(
                $expectedValue,
                $attribute->getDataUsingMethod($key),
                "Invalid expected value for $key field."
            );
        }
    }

    /**
     * Checks that appropriate error message appears.
     *
     * @param string $errorMessage
     * @return void
     */
    protected function assertErrorSessionMessages(string $errorMessage): void
    {
        $this->assertSessionMessages(
            $this->equalTo([$this->escaper->escapeHtml($errorMessage)]),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Create or update attribute using catalog/product_attribute/save action.
     *
     * @param array $attributeData
     * @param int|null $attributeId
     * @return void
     */
    private function dispatchAttributeSave(array $attributeData, ?int $attributeId = null): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($attributeData);
        if ($attributeId) {
            $this->getRequest()->setParam('attribute_id', $attributeId);
        }
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

    /**
     * Set default values of attribute store labels and save.
     *
     * @param string $attributeCode
     * @return void
     */
    private function setAttributeStoreLabels(string $attributeCode): void
    {
        $stores = $this->storeManager->getStores();
        $storeLabels = [];
        foreach ($stores as $storeId => $store) {
            $storeLabels[$storeId] = $store->getName();
        }
        $attribute = $this->productAttributeRepository->get($attributeCode);
        $attribute->setStoreLabels($storeLabels);
        $this->productAttributeRepository->save($attribute);
    }

    /**
     * Check that the attribute update was successful after adding it to the
     * new attribute set and new attribute group.
     *
     * @param ProductAttributeInterface|Attribute $attribute
     * @param array $postData
     * @return void
     */
    private function assertUpdateAttributeSet(
        ProductAttributeInterface $attribute,
        array $postData
    ): void {
        $attributeSet = $this->getAttributeSetByName->execute($postData['new_attribute_set_name']);
        $this->assertNotNull(
            $attributeSet,
            'The attribute set ' . $postData['new_attribute_set_name'] . 'was not created'
        );

        $attributeGroup = $this->getAttributeGroupByName->execute((int)$attributeSet->getId(), $postData['groupName']);
        $this->assertNotNull(
            $attributeGroup,
            'The attribute group ' . $postData['groupName'] . 'was not created'
        );

        $entityAttributeId = $this->getEntityIdByAttributeId->execute(
            (int)$attributeSet->getId(),
            (int)$attribute->getId(),
            (int)$attributeGroup->getId()
        );

        $this->assertNotNull(
            $entityAttributeId,
            'The attribute set and attribute group for the current attribute have not been updated.'
        );
    }

    /**
     * Check that attribute options are saved correctly.
     *
     * @param ProductAttributeInterface|Attribute $attribute
     * @param array $expectedData
     * @return void
     */
    private function assertUpdateAttributeOptions(
        ProductAttributeInterface $attribute,
        array $expectedData
    ): void {
        $actualOptionsData = $this->getActualOptionsData($attribute->getId());

        $this->assertEquals($expectedData, $actualOptionsData, 'Expected attribute options does not match.');
    }

    /**
     * Get attribute options by attribute id and store id.
     *
     * @param string $attributeId
     * @param int|null $storeId
     * @return array
     */
    private function getAttributeOptions(string $attributeId, ?int $storeId = null): array
    {
        $attributeOptionCollection = $this->optionCollectionFactory->create();
        $attributeOptionCollection->setAttributeFilter($attributeId);
        $attributeOptionCollection->setStoreFilter($storeId);

        return $attributeOptionCollection->getItems();
    }

    /**
     * Get attribute option values by option id.
     *
     * @param int $optionId
     * @return array
     */
    private function getAttributeOptionValues(int $optionId): array
    {
        $connection = $this->attributeOptionResource->getConnection();
        $select = $connection->select()
            ->from(
                ['main_table' => $this->attributeOptionResource->getTable('eav_attribute_option_value')],
                ['store_id','value']
            )
            ->where('main_table.option_id = ?', $optionId);

        return $connection->fetchPairs($select);
    }
}
