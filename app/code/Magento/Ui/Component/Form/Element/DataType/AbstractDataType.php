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
 * phpcs:disable Magento2.Classes.AbstractApi
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
