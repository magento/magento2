<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Xsd\Media;

/**
 * Interface that encapsulates complexity of expression computation
 *
 * @api
 */
interface TypeDataExtractorInterface
{
    /**
     * Extract media configuration data from the DOM structure
     *
     * @param \DOMElement $childNode
     * @param string $mediaParentTag
     * @return mixed
     */
    public function process(\DOMElement $childNode, $mediaParentTag);
}
