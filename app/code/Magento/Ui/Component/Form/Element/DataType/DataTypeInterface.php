<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Ui\Component\Form\Element\DataType;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface DataTypeInterface
 *
 * @api
 */
interface DataTypeInterface extends UiComponentInterface
{
    /**
     * Validate data
     *
     * @return bool
     */
    public function validate();
}
