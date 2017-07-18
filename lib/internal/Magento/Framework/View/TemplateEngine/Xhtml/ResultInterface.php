<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml;

/**
 * Interface ResultInterface
 */
interface ResultInterface
{
    /**
     * Get result document root element \DOMElement
     *
     * @return \DOMElement
     */
    public function getDocumentElement();

    /**
     * Append layout configuration
     *
     * @return void
     */
    public function appendLayoutConfiguration();

    /**
     * Returns the string representation
     *
     * @return string
     */
    public function __toString();
}
