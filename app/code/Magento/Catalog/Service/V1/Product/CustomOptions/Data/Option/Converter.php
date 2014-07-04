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

namespace Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option;

use \Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option\Metadata\ConverterInterface as MetadataConverter;

class Converter
{
    /**
     * @var MetadataConverter
     */
    protected $metadataConverter;

    /**
     * @param MetadataConverter $valueConverter
     */
    public function __construct(MetadataConverter $valueConverter)
    {
        $this->metadataConverter = $valueConverter;
    }

    /**
     * Convert data object to array
     *
     * @param \Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option $option
     * @return array
     */
    public function convert(\Magento\Catalog\Service\V1\Product\CustomOptions\Data\Option $option)
    {
        $output = [
            'option_id' => $option->getOptionId(),
            'title' => $option->getTitle(),
            'type' => $option->getType(),
            'sort_order' => $option->getSortOrder(),
            'is_require' => $option->getIsRequire()
        ];
        $output = array_merge($output, $this->metadataConverter->convert($option));
        return $output;
    }
}
