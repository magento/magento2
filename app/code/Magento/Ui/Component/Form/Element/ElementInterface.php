<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form\Element;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface ElementInterface
 */
interface ElementInterface extends UiComponentInterface
{
    /**
     * @return string
     */
    public function getHtmlId();

    /**
     * @return string
     */
    public function getFormInputName();

    /**
     * @return bool
     */
    public function isReadonly();

    /**
     * @return string
     */
    public function getCssClasses();
}
