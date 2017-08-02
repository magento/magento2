<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface ElementInterface
 * @api
 * @since 2.0.0
 */
interface ElementInterface extends UiComponentInterface
{
    /**
     * @return string
     * @since 2.0.0
     */
    public function getHtmlId();

    /**
     * @return string
     * @since 2.0.0
     */
    public function getFormInputName();

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isReadonly();

    /**
     * @return string
     * @since 2.0.0
     */
    public function getCssClasses();
}
