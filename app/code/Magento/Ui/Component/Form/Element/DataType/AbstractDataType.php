<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element\DataType;

use Magento\Ui\Component\AbstractView;

/**
 * Class AbstractDataType
 */
abstract class AbstractDataType extends AbstractView implements DataTypeInterface
{
    /**
     * @return bool
     */
    public function validate()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getDataObjectValue()
    {
        return $this->getData('data_object')[$this->getData('name')];
    }
}
