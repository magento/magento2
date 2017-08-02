<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml;

/**
 * Interface ResultInterface
 * @since 2.0.0
 */
interface ResultInterface
{
    /**
     * Get result document root element \DOMElement
     *
     * @return \DOMElement
     * @since 2.0.0
     */
    public function getDocumentElement();

    /**
     * Append layout configuration
     *
     * @return void
     * @since 2.0.0
     */
    public function appendLayoutConfiguration();

    /**
     * Returns the string representation
     *
     * @return string
     * @since 2.0.0
     */
    public function __toString();
}
