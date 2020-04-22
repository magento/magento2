<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../Store/_files/core_fixturestore.php';

$objectManager = Bootstrap::getObjectManager();
$defaultInstalledStoreId = $storeManager->getStore('default')->getId();
$secondStoreId = $storeManager->getStore('fixturestore')->getId();
/** @var CategorySetup $installer */
$installer = $objectManager->get(CategorySetup::class);
/** @var Attribute $attribute */
$attribute = $objectManager->get(AttributeFactory::class)->create();
/** @var ProductAttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->get(ProductAttributeRepositoryInterface::class);
$entityType = $installer->getEntityTypeId(ProductAttributeInterface::ENTITY_TYPE_CODE);
if (!$attribute->loadByCode($entityType, 'different_labels_attribute')->getAttributeId()) {
    $attribute->setData(
        [
            'frontend_label' => ['Different option labels dropdown attribute'],
            'entity_type_id' => $entityType,
            'frontend_input' => 'select',
            'backend_type' => 'int',
            'is_required' => '0',
            'attribute_code' => 'different_labels_attribute',
            'is_global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            'is_user_defined' => 1,
            'is_unique' => '0',
            'is_searchable' => '0',
            'is_comparable' => '0',
            'is_filterable' => '1',
            'is_filterable_in_search' => '0',
            'is_used_for_promo_rules' => '0',
            'is_html_allowed_on_front' => '1',
            'used_in_product_listing' => '1',
            'used_for_sort_by' => '0',
            'option' => [
                'value' => [
                    'option_1' => [
                        Store::DEFAULT_STORE_ID => 'Option 1',
                        $defaultInstalledStoreId => 'Option 1 Default Store',
                        $secondStoreId => 'Option 1 Second Store',
                    ],
                    'option_2' => [
                        Store::DEFAULT_STORE_ID => 'Option 2',
                        $defaultInstalledStoreId => 'Option 2 Default Store',
                        $secondStoreId => 'Option 2 Second Store',
                    ],
                    'option_3' => [
                        Store::DEFAULT_STORE_ID => 'Option 3',
                        $defaultInstalledStoreId => 'Option 3 Default Store',
                        $secondStoreId => 'Option 3 Second Store',
                    ],
                ],
                'order' => [
                    'option_1' => 1,
                    'option_2' => 2,
                    'option_3' => 3,
                ],
            ],
        ]
    );
    $attributeRepository->save($attribute);
    $installer->addAttributeToGroup(
        ProductAttributeInterface::ENTITY_TYPE_CODE,
        'Default',
        'General',
        $attribute->getId()
    );
}
