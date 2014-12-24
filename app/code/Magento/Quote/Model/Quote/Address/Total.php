<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
