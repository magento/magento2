<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element;

use Magento\Ui\Component\AbstractComponent;

/**
 * Class AbstractElement
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class AbstractElement extends AbstractComponent implements ElementInterface
{
    /**
     * Get html id
     *
     * @return string
     */
    public function getHtmlId()
    {
        return '';
    }

    /**
     * Get value
     *
     * @return string|int
     */
    public function getValue()
    {
        return $this->getData('value');
    }

    /**
     * Get form input name
     *
     * @return string
     */
    public function getFormInputName()
    {
        return $this->getData('input_name');
    }

    /**
     * Is read only
     *
     * @return bool
     */
    public function isReadonly()
    {
        return (bool) $this->getData('readonly');
    }

    /**
     * Get css classes
     *
     * @return string
     */
    public function getCssClasses()
    {
        return $this->getData('css_classes');
    }
}
