<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Template Types interface
 *
 * @deprecated 2.2.0 since 2.2.0 because of incorrect location
 * @since 2.0.0
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
     * @since 2.0.0
     */
    public function isPlain();

    /**
     * Getter for template type
     *
     * @return int
     * @since 2.0.0
     */
    public function getType();
}
