<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
