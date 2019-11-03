<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Variable\Model\Variable;

/**
 * The class purpose is returns
 */
class Data
{
    /**
     * @var \Magento\Variable\Model\ResourceModel\Variable\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Variable\Model\Source\Variables
     */
    private $storesVariables;

    /**
     * @param \Magento\Variable\Model\ResourceModel\Variable\CollectionFactory $collectionFactory
     * @param \Magento\Variable\Model\Source\Variables $storesVariables
     */
    public function __construct(
        \Magento\Variable\Model\ResourceModel\Variable\CollectionFactory $collectionFactory,
        \Magento\Variable\Model\Source\Variables $storesVariables
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storesVariables = $storesVariables;
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
                'variable_type' => \Magento\Variable\Model\Source\Variables::DEFAULT_VARIABLE_TYPE
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
        /** @var \Magento\Variable\Model\ResourceModel\Variable\Collection $customVariables */
        $customVariables = $this->collectionFactory->create();

        $variables = [];
        foreach ($customVariables->getData() as $variable) {
            $variables[] = [
                'code' => $variable['code'],
                'variable_name' => __('Custom Variable') . ' / ' . $variable['name'],
                'variable_type' => \Magento\Variable\Model\Source\Variables::CUSTOM_VARIABLE_TYPE
            ];
        }

        return $variables;
    }
}
