<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Increment;

/**
 * Enter description here...
 *
 * Properties:
 * - prefix
 * - pad_length
 * - pad_char
 * - last_id
 *
 * @api
 * @since 100.0.2
 */
class NumericValue extends \Magento\Eav\Model\Entity\Increment\AbstractIncrement
{
    /**
     * Get next id
     *
     * @return string
     */
    public function getNextId()
    {
        $last = $this->getLastId();
        $prefix = (string)$this->getPrefix();

        if (is_string($last) && '' !== $last && strpos($last, $prefix) === 0) {
            $last = (int)substr($last, strlen($prefix));
        } else {
            $last = (int)$last;
        }

        $next = $last + 1;

        return $this->format($next);
    }
}
