<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider\Modifier;

/**
 * Interface \Magento\Ui\DataProvider\Modifier\PoolInterface
 *
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
