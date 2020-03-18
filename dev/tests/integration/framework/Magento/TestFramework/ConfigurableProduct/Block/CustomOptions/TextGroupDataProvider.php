<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\ConfigurableProduct\Block\CustomOptions;

use Magento\TestFramework\Catalog\Block\Product\View\Options\TextGroupDataProvider as OptionsTextGroupDataProvider;

/**
 * @inheritdoc
 */
class TextGroupDataProvider extends OptionsTextGroupDataProvider
{
    /**
     * @inheritdoc
     */
    public function getData(): array
    {
        $optionsData = parent::getData();
        unset(
            $optionsData['type_field_percent_price'],
            $optionsData['type_area_percent_price']
        );

        return $optionsData;
    }
}
