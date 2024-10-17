<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Variable\Model\Variable;

use Magento\Variable\Model\ResourceModel\Variable\Collection;
use Magento\Variable\Model\ResourceModel\Variable\CollectionFactory;
use Magento\Variable\Model\Source\Variables;

/**
 * The class purpose is returns
 */
class Data
{
    /**
     * @param CollectionFactory $collectionFactory
     * @param Variables $storesVariables
     */
    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        private readonly Variables $storesVariables
    ) {
    }

    /**
     * Prepare default variables
     *
     * @return array
     */
    public function getDefaultVariables()
    {
        $variables = [];
        foreach ($this->storesVariables->getData() as $variable) {
            $variables[] = [
                'code' => $variable['value'],
                'variable_name' => $variable['group_label'] . ' / ' . $variable['label'],
                'variable_type' => Variables::DEFAULT_VARIABLE_TYPE
            ];
        }

        return $variables;
    }

    /**
     * Prepare custom variables
     *
     * @return array
     */
    public function getCustomVariables()
    {
        /** @var Collection $customVariables */
        $customVariables = $this->collectionFactory->create();

        $variables = [];
        foreach ($customVariables->getData() as $variable) {
            $variables[] = [
                'code' => $variable['code'],
                'variable_name' => __('Custom Variable') . ' / ' . $variable['name'],
                'variable_type' => Variables::CUSTOM_VARIABLE_TYPE
            ];
        }

        return $variables;
    }
}
