<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\ConfigurableProduct\Block\CustomOptions;

use Magento\TestFramework\Catalog\Block\Product\View\Options\DateGroupDataProvider as OptionsDateGroupDataProvider;

/**
 * @inheritdoc
 */
class DateGroupDataProvider extends OptionsDateGroupDataProvider
{
    /**
     * @inheritdoc
     */
    public function getData(): array
    {
        $optionsData = parent::getData();
        unset(
            $optionsData['type_date_percent_price'],
            $optionsData['type_date_and_time_percent_price'],
            $optionsData['type_time_percent_price']
        );

        return $optionsData;
    }
}
