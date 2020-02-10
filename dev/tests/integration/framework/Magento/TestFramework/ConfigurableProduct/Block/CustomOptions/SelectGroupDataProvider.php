<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\ConfigurableProduct\Block\CustomOptions;

use Magento\TestFramework\Catalog\Block\Product\View\Options\SelectGroupDataProvider as OptionsSelectGroupDataProvider;

/**
 * @inheritdoc
 */
class SelectGroupDataProvider extends OptionsSelectGroupDataProvider
{
    /**
     * @inheritdoc
     */
    public function getData(): array
    {
        $optionsData = parent::getData();
        unset(
            $optionsData['type_drop_down_value_percent_price'],
            $optionsData['type_radio_button_value_percent_price'],
            $optionsData['type_checkbox_value_percent_price'],
            $optionsData['type_multiselect_value_percent_price']
        );

        return $optionsData;
    }
}
