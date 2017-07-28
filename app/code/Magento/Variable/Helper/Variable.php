<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Helper;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Variable\Model\Variable as VariableModel;

class Variable
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Variable\Model\Variable
     */
    protected $variableModel;

    /**
     * VariableHelper constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Variable\Model\Variable $variableModel
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        VariableModel $variableModel
    ) {
        $this->storeManager = $storeManager;
        $this->variableModel = $variableModel;
    }

    /**
     * Check if the given store (or the current one) has the given
     * variable set with the given text value
     *
     * @param string $variableCode
     * @param string $expectedValue
     * @param string $type
     * @param int $storeId
     *
     * @return boolean
     */
    public function customVariableHasValue(
        $variableCode,
        $expectedValue,
        $type = VariableModel::TYPE_TEXT,
        $storeId = null
    ) {
        $status = false;

        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $this->variableModel
            ->setStoreId($storeId)
            ->loadByCode($variableCode);

        $value = $this->variableModel->getValue($type);

        if ($value && $value === (string) $expectedValue) {
            $status = true;
        }

        return $status;
    }
}
