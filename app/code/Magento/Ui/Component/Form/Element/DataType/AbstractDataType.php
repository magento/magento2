<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Ui\Component\Form\Element\DataType;

use Magento\Ui\Component\AbstractComponent;

/**
 * Class AbstractDataType
 *
 * @api
 * @since 100.0.2
 */
abstract class AbstractDataType extends AbstractComponent implements DataTypeInterface
{
    /**
     * Validate value
     *
     * @return bool
     */
    public function validate()
    {
        return true;
    }
}
