<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element\DataType;

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
}
