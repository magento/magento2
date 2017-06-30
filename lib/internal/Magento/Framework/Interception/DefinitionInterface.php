<?php
/**
 * Plugin method definitions. Provide the list of interception methods in specified plugin.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
