<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Xsd\Media;

/**
 * Interface that encapsulates complexity of expression computation
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
