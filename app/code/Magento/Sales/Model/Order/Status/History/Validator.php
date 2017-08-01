<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Status\History;

use Magento\Sales\Model\Order\Status\History;

/**
 * Class Validator
 * @package Magento\Sales\Model\Order\Status\History
 * @since 2.0.0
 */
class Validator
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $requiredFields = ['parent_id' => 'Order Id'];

    /**
     * @param History $history
     * @return array
     * @since 2.0.0
     */
    public function validate(History $history)
    {
        $warnings = [];
        foreach ($this->requiredFields as $code => $label) {
            if (!$history->hasData($code)) {
                $warnings[] = sprintf('%s is a required field', $label);
            }
        }
        return $warnings;
    }
}
