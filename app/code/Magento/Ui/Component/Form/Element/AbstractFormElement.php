<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element;

use Magento\Ui\Component\AbstractView;

/**
 * Class AbstractFormElement
 */
abstract class AbstractFormElement extends AbstractView implements ElementInterface
{
    /**
     * @return string
     */
    public function getHtmlId()
    {
        return '';
    }

    /**
     * @return string|int
     */
    public function getValue()
    {
        return $this->getData('value');
    }

    /**
     * @return string
     */
    public function getFormInputName()
    {
        return $this->getData('input_name');
    }

    /**
     * @return bool
     */
    public function getIsReadonly()
    {
        return (bool) $this->getData('readonly');
    }

    /**
     * @return string
     */
    public function getCssClasses()
    {
        return $this->getData('css_classes');
    }
}
