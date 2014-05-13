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

/** @var $installer \Magento\Catalog\Model\Resource\Setup */
$installer = $this;

$eavResource = $installer->createEavAttributeResource();

$multiSelectAttributeCodes = $eavResource->getAttributeCodesByFrontendType('multiselect');

foreach ($multiSelectAttributeCodes as $attributeCode) {
    /** @var $attribute \Magento\Catalog\Model\Resource\Eav\Attribute */
    $attribute = $installer->getAttribute('catalog_product', $attributeCode);
    if ($attribute) {
        $attributeTable = $installer->getAttributeTable('catalog_product', $attributeCode);
        $select = $installer->getConnection()->select()->from(
            array('e' => $attributeTable)
        )->where(
            "e.attribute_id=?",
            $attribute['attribute_id']
        )->where(
            'e.value LIKE "%,,%"'
        );
        $result = $installer->getConnection()->fetchAll($select);

        if ($result) {
            foreach ($result as $row) {
                if (isset($row['value']) && !empty($row['value'])) {
                    // 1,2,,,3,5 --> 1,2,3,5
                    $row['value'] = preg_replace('/,{2,}/', ',', $row['value'], -1, $replaceCnt);

                    if ($replaceCnt) {
                        $installer->getConnection()->update(
                            $attributeTable,
                            array('value' => $row['value']),
                            "value_id=" . $row['value_id']
                        );
                    }
                }
            }
        }
    }
}
