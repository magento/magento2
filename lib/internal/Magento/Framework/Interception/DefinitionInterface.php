<?php
/**
 * Plugin method definitions. Provide the list of interception methods in specified plugin.
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception;

/**
 * Interface \Magento\Framework\Interception\DefinitionInterface
 *
 * @api
 */
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
