<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
