<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\View\Layout\Argument;

/**
 * Interface of value modification with no value loss
 */
interface UpdaterInterface
{
    /**
     * Return modified version of an input value
     *
     * @param mixed $value
     * @return mixed
     */
    public function update($value);
}
