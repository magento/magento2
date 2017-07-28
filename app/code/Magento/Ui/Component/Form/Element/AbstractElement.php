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
 * @api
 * @since 2.0.0
 */
abstract class AbstractElement extends AbstractComponent implements ElementInterface
{
    /**
     * @return string
     * @since 2.0.0
     */
    public function getHtmlId()
    {
        return '';
    }

    /**
     * @return string|int
     * @since 2.0.0
     */
    public function getValue()
    {
        return $this->getData('value');
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getFormInputName()
    {
        return $this->getData('input_name');
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isReadonly()
    {
        return (bool) $this->getData('readonly');
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getCssClasses()
    {
        return $this->getData('css_classes');
    }
}
