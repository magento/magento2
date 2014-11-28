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

namespace Magento\Catalog\Model\ProductOptions;

class TypeList implements \Magento\Catalog\Api\ProductCustomOptionTypeListInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\Catalog\Api\Data\ProductCustomOptionTypeDataBuilder
     */
    protected $builder;

    /**
     * @param Config $config
     * @param \Magento\Catalog\Api\Data\ProductCustomOptionTypeDataBuilder $builder
     */
    public function __construct(
        Config $config,
        \Magento\Catalog\Api\Data\ProductCustomOptionTypeDataBuilder $builder
    ) {
        $this->config = $config;
        $this->builder = $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $output = [];
        foreach ($this->config->getAll() as $option) {
            foreach ($option['types'] as $type) {
                if ($type['disabled']) {
                    continue;
                }
                $itemData = [
                    'label' => __($type['label']),
                    'code' => $type['name'],
                    'group' => __($option['label'])
                ];
                $output[] = $this->builder->populateWithArray($itemData)->create();
            }
        }
        return $output;
    }
}
