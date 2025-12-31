<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\TemplateEngine\Xhtml;

/**
 * Interface ResultInterface
 *
 * @api
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
