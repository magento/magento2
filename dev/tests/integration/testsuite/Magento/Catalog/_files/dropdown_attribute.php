<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* Create attribute */
/** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
$attribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
);

if (!$attribute->loadByCode(4, 'dropdown_attribute')->getId()) {
	/** @var $installer \Magento\Catalog\Setup\CategorySetup */
	$installer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
		\Magento\Catalog\Setup\CategorySetup::class
	);

	$attribute->setData(
		[
			'attribute_code'                => 'dropdown_attribute',
			'entity_type_id'                => $installer->getEntityTypeId('catalog_product'),
			'is_global'                     => 0,
			'is_user_defined'               => 1,
			'frontend_input'                => 'select',
			'is_unique'                     => 0,
			'is_required'                   => 0,
			'is_searchable'                 => 0,
			'is_visible_in_advanced_search' => 0,
			'is_comparable'                 => 0,
			'is_filterable'                 => 0,
			'is_filterable_in_search'       => 0,
			'is_used_for_promo_rules'       => 0,
			'is_html_allowed_on_front'      => 1,
			'is_visible_on_front'           => 0,
			'used_in_product_listing'       => 0,
			'used_for_sort_by'              => 0,
			'frontend_label'                => ['Drop-Down Attribute'],
			'backend_type'                  => 'varchar',
			'backend_model'                 => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
			'option'                        => [
				'value' => [
					'option_1' => ['Option 1'],
					'option_2' => ['Option 2'],
					'option_3' => ['Option 3'],
				],
				'order' => [
					'option_1' => 1,
					'option_2' => 2,
					'option_3' => 3,
				],
			],
		]
	);
	$attribute->save();

	/* Assign attribute to attribute set */
	$installer->addAttributeToGroup('catalog_product', 'Default', 'Attributes', $attribute->getId());
}
