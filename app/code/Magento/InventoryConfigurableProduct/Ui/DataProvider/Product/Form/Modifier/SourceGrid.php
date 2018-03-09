<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\ConfigurablePanel;

class SourceGrid extends AbstractModifier
{
    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        $additionalMatrixConfig = [
            'component' => 'Magento_InventoryConfigurableProduct/js/components/dynamic-rows-configurable',
            'matrixIndex' => ConfigurablePanel::CONFIGURABLE_MATRIX,
            'sourcesIndex' => 'sources',
        ];

        $matrix = $meta[ConfigurablePanel::GROUP_CONFIGURABLE]['children'][ConfigurablePanel::CONFIGURABLE_MATRIX];

        $matrix['arguments']['data']['config'] = array_merge(
            $matrix['arguments']['data']['config'],
            $additionalMatrixConfig
        );

        $meta[ConfigurablePanel::GROUP_CONFIGURABLE]['children'][ConfigurablePanel::CONFIGURABLE_MATRIX] = $matrix;

        return $meta;
    }
}
