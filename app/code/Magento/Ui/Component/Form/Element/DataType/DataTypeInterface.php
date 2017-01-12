<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
