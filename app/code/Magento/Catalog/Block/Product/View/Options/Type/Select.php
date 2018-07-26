<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Product\View\Options\Type;

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Block\Product\View\Options\View\Checkable;
use Magento\Catalog\Block\Product\View\Options\View\Multiple;
use Magento\Framework\App\ObjectManager;

/**
 * Product options text type block
 *
 * @api
 * @since 100.0.2
 */
class Select extends \Magento\Catalog\Block\Product\View\Options\AbstractOptions
{
    /**
     * Return html for control element
     *
     * @return string
     */
    public function getValuesHtml()
    {
        $option = $this->getOption();
        $optionType = $option->getType();
        $objectManager = ObjectManager::getInstance();
        // Remove inline prototype onclick and onchange events

        if ($optionType === ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN ||
            $optionType === ProductCustomOptionInterface::OPTION_TYPE_MULTIPLE
        ) {
            $optionBlock = $objectManager->create(
                Multiple::class,
                [
                    'option' => $option
                ]
            );
        }

        if ($optionType === ProductCustomOptionInterface::OPTION_TYPE_RADIO ||
            $optionType === ProductCustomOptionInterface::OPTION_TYPE_CHECKBOX
        ) {
            $optionBlock = $objectManager->create(
                Checkable::class,
                [
                    'option' => $option
                ]
            );
        }

        return $optionBlock
            ->setOption($option)
            ->setProduct($this->getProduct())
            ->setSkipJsReloadPrice(1)
            ->_toHtml();
    }
}
