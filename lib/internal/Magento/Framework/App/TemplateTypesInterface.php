<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Template Types interface
 *
 * @deprecated 100.2.0 because of incorrect location
 */
interface TemplateTypesInterface
{
    /**
     * Types of template
     */
    const TYPE_TEXT = 1;

    const TYPE_HTML = 2;

    /**
     * Return true if template type eq text
     *
     * @return boolean
     */
    public function isPlain();

    /**
     * Getter for template type
     *
     * @return int
     */
    public function getType();
}
