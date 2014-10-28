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
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Handler\ProductType;

use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\HandlerInterface;
use Magento\Catalog\Model\Product;

class Configurable implements HandlerInterface
{
    /**
     * Handle data received from Associated Products tab of configurable product
     *
     * @param Product $product
     * @return void
     */
    public function handle(Product $product)
    {
        if ($product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return;
        }

        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $type */
        $type = $product->getTypeInstance();
        $originalAttributes = $type->getConfigurableAttributesAsArray($product);
        // Organize main information about original product attributes in assoc array form
        $originalAttributesMainInfo = array();
        if (is_array($originalAttributes)) {
            foreach ($originalAttributes as $originalAttribute) {
                $originalAttributesMainInfo[$originalAttribute['id']] = array();
                foreach ($originalAttribute['values'] as $value) {
                    $originalAttributesMainInfo[$originalAttribute['id']][$value['value_index']] = array(
                        'is_percent' => $value['is_percent'],
                        'pricing_value' => $value['pricing_value']
                    );
                }
            }
        }
        $attributeData = $product->getConfigurableAttributesData();
        if (is_array($attributeData)) {
            foreach ($attributeData as &$data) {
                $id = $data['attribute_id'];
                foreach ($data['values'] as &$value) {
                    $valueIndex = $value['value_index'];
                    if (isset($originalAttributesMainInfo[$id][$valueIndex])) {
                        $value['pricing_value'] = $originalAttributesMainInfo[$id][$valueIndex]['pricing_value'];
                        $value['is_percent'] = $originalAttributesMainInfo[$id][$valueIndex]['is_percent'];
                    } else {
                        $value['pricing_value'] = 0;
                        $value['is_percent'] = 0;
                    }
                }
            }
            $product->setConfigurableAttributesData($attributeData);
        }
    }
}
