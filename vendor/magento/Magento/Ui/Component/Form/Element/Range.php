<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Ui\Component\Form\Element;

/**
 * Class Range
 */
class Range extends AbstractFormElement
{
    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->getData('input_type');
    }
}
