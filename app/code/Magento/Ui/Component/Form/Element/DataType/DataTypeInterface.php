<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element\DataType;

use Magento\Framework\Object;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface DataTypeInterface
 */
interface DataTypeInterface extends UiComponentInterface
{
    /**
     * Validate data
     *
     * @return bool
     */
    public function validate();

    /**
     * Get data object value
     *
     * @return mixed
     */
    public function getDataObjectValue();
}
