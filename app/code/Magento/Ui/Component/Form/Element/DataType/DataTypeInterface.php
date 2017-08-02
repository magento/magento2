<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element\DataType;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface DataTypeInterface
 * @since 2.0.0
 */
interface DataTypeInterface extends UiComponentInterface
{
    /**
     * Validate data
     *
     * @return bool
     * @since 2.0.0
     */
    public function validate();
}
