<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address;

class Total extends \Magento\Framework\Object
{
    /**
     * Merge numeric total values
     *
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function merge(\Magento\Quote\Model\Quote\Address\Total $total)
    {
        $newData = $total->getData();
        foreach ($newData as $key => $value) {
            if (is_numeric($value)) {
                $this->setData($key, $this->_getData($key) + $value);
            }
        }
        return $this;
    }
}
