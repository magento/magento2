<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\ConfigurableProduct\Block\CustomOptions;

use Magento\TestFramework\Catalog\Block\Product\View\Options\FileGroupDataProvider as OptionsFileGroupDataProvider;

/**
 * @inheritdoc
 */
class FileGroupDataProvider extends OptionsFileGroupDataProvider
{
    /**
     * @inheritdoc
     */
    public function getData(): array
    {
        $optionsData = parent::getData();
        unset($optionsData['type_file_percent_price']);

        return $optionsData;
    }
}
