<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
 */
class Numeric extends \Magento\Eav\Model\Entity\Increment\AbstractIncrement
{
    /**
     * Get next id
     *
     * @return string
     */
    public function getNextId()
    {
        $last = $this->getLastId();

        if (strpos($last, $this->getPrefix()) === 0) {
            $last = (int)substr($last, strlen($this->getPrefix()));
        } else {
            $last = (int)$last;
        }

        $next = $last + 1;

        return $this->format($next);
    }
}
