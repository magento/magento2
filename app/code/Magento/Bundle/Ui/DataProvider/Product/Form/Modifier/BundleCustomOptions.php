<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use Magento\Ui\Component\Container;

/**
 * Customize "Customizable Options" panel
 */
class BundleCustomOptions extends AbstractModifier
{
    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        if ($groupCode =  $this->getGroupCodeByField($meta, CustomOptions::CONTAINER_HEADER_NAME)) {
            $meta[$groupCode]['children']['message'] = $this->getErrorMessage(0);

            if (!empty($meta[$groupCode]['children'][CustomOptions::CONTAINER_HEADER_NAME])) {
                $meta = $this->modifyCustomOptionsButton(
                    $meta,
                    $groupCode,
                    CustomOptions::CONTAINER_HEADER_NAME,
                    CustomOptions::BUTTON_IMPORT
                );
                $meta = $this->modifyCustomOptionsButton(
                    $meta,
                    $groupCode,
                    CustomOptions::CONTAINER_HEADER_NAME,
                    CustomOptions::BUTTON_ADD
                );
            }
        }

        return $meta;
    }

    /**
     * Add visible configuration for the Custom Options buttons
     *
     * @param array $meta
     * @param string $group
     * @param string $container
     * @param string $button
     * @return array
     */
    public function modifyCustomOptionsButton(array $meta, $group, $container, $button)
    {
        if (!empty($meta[$group]['children'][$container]['children'][$button])) {
            $meta[$group]['children'][$container]['children'][$button]['arguments']['data']['config']['imports'] = [
                'visible' => '!ns = ${ $.ns }, index = ' . BundlePrice::CODE_PRICE_TYPE . ':checked',
            ];
        }
        return $meta;
    }

    /**
     * Prepares configuration for the error message container
     *
     * @param int $sortOrder
     * @return array
     */
    public function getErrorMessage($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'component' => 'Magento_Ui/js/form/components/html',
                        'componentType' => Container::NAME,
                        'additionalClasses' => 'message message-error',
                        'content' => __('We can\'t save custom-defined options for bundles with dynamic pricing.'),
                        'sortOrder' => $sortOrder,
                        'imports' => [
                            'visible' => 'ns = ${ $.ns }, index = ' . BundlePrice::CODE_PRICE_TYPE . ':checked',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }
}
