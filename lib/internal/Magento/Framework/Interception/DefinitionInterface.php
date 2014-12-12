<?php
/**
 * Plugin method definitions. Provide the list of interception methods in specified plugin.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Interception;

interface DefinitionInterface
{
    const LISTENER_BEFORE = 1;

    const LISTENER_AROUND = 2;

    const LISTENER_AFTER = 4;

    /**
     * Retrieve list of methods
     *
     * @param string $type
     * @return string[]
     */
    public function getMethodList($type);
}
