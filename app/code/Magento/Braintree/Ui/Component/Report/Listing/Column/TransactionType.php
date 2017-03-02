<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Ui\Component\Report\Listing\Column;

use Braintree\Transaction;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Type
 */
class TransactionType implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $types = $this->getAvailableTransactionTypes();
        foreach ($types as $typeCode => $typeName) {
            $this->options[$typeCode]['label'] = $typeName;
            $this->options[$typeCode]['value'] = $typeCode;
        }

        return $this->options;
    }

    /**
     * @return array
     */
    private function getAvailableTransactionTypes()
    {
        return [
            Transaction::SALE => __(Transaction::SALE),
            Transaction::CREDIT => __(Transaction::CREDIT)
        ];
    }
}
