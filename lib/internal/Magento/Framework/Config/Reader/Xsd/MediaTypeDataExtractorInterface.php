<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Reader\Xsd;

interface MediaTypeDataExtractorInterface
{
    public function process(\DOMElement $childNode);
}
