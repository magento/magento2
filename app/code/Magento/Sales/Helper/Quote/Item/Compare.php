<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Helper\Quote\Item;

use Magento\Sales\Model\Quote\Item;

/**
 * Class Compare
 */
class Compare
{
    /**
     * Returns option values adopted to compare
     *
     * @param mixed $value
     * @return mixed
     */
    protected function getOptionValues($value)
    {
        if (is_string($value) && is_array(@unserialize($value))) {
            $value = @unserialize($value);
            unset($value['qty'], $value['uenc']);
        }
        return $value;
    }

    /**
     * Compare two quote items
     *
     * @param Item $target
     * @param Item $compared
     * @return bool
     */
    public function compare(Item $target, Item $compared)
    {
        if ($target->getProductId() != $compared->getProductId()) {
            return false;
        }
        $targetOptions = $this->getOptions($target);
        $comparedOptions = $this->getOptions($compared);

        if (array_diff_key($targetOptions, $comparedOptions) != array_diff_key($comparedOptions, $targetOptions)
        ) {
            return false;
        }
        foreach ($targetOptions as $name => $value) {
            if ($comparedOptions[$name] != $value) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns options adopted to compare
     *
     * @param Item $item
     * @return array
     */
    public function getOptions(Item $item)
    {
        $options = [];
        foreach ($item->getOptions() as $option) {
            $options[$option->getCode()] = $this->getOptionValues($option->getValue());
        }
        return $options;
    }
}
