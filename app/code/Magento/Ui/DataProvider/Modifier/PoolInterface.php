<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Ui\DataProvider\Modifier;

/**
 * Interface \Magento\Ui\DataProvider\Modifier\PoolInterface
 *
 * @api
 */
interface PoolInterface
{
    /**
     * Retrieve modifiers
     *
     * @return array
     */
    public function getModifiers();

    /**
     * Retrieve modifiers instantiated
     *
     * @return ModifierInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getModifiersInstances();
}
