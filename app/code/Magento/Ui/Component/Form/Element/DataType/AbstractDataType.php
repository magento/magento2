<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element\DataType;

use Magento\Ui\Component\AbstractComponent;

/**
 * Class AbstractDataType
 *
 * @api
 * @since 2.0.0
 */
abstract class AbstractDataType extends AbstractComponent implements DataTypeInterface
{
    /**
     * Validate value
     *
     * @return bool
     * @since 2.0.0
     */
    public function validate()
    {
        return true;
    }
}
