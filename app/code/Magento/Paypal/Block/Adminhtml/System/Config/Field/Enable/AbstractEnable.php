<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Adminhtml\System\Config\Field\Enable;

use Magento\Config\Block\System\Config\Form\Field;

/**
 * Class AbstractEnable
 */
abstract class AbstractEnable extends Field
{
    /**
     * Retrieve data-ui-id attribute
     *
     * Retrieve data-ui-id attribute which will distinguish
     * link/input/container/anything else in template among others.
     * Function takes an arbitrary amount of parameters.
     *
     * @param string|null $arg1
     * @param string|null $arg2
     * @param string|null $arg3
     * @param string|null $arg4
     * @param string|null $arg5
     * @return string
     */
    public function getUiId($arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null, $arg5 = null)
    {
        return parent::getUiId($arg1, $arg2, $arg3, $arg4, $arg5)
            . 'data-enable="' . $this->getDataAttributeName(). '" ';
    }

    /**
     * Retrieve HTML markup for given form element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->setRenderer($this);
        return parent::render($element);
    }

    /**
     * Getting the name of a UI attribute
     *
     * @return string
     */
    abstract protected function getDataAttributeName();
}
