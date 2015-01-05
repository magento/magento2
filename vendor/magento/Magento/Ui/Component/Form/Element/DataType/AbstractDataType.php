<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
