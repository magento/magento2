<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\TestFramework\CodingStandard\Tool;

interface ExtensionInterface
{
    /**
     * Set extensions for tool to run
     * Example: 'php', 'xml', 'phtml', 'css'
     *
     * @param array $extensions
     * @return void
     */
    public function setExtensions(array $extensions);
}
