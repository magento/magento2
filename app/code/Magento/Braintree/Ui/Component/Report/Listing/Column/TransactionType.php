<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Ui\Component\Report\Listing\Column;

use Braintree\Transaction;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Type
 *
 * @deprecated Starting from Magento 2.3.6 Braintree payment method core integration is deprecated
 * in favor of official payment integration available on the marketplace
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
        // @codingStandardsIgnoreStart
        return [
            Transaction::SALE => __(Transaction::SALE),
            Transaction::CREDIT => __(Transaction::CREDIT)
        ];
        // @codingStandardsIgnoreEnd
    }
}
