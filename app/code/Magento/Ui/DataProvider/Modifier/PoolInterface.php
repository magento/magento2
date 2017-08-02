<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider\Modifier;

/**
 * Interface \Magento\Ui\DataProvider\Modifier\PoolInterface
 *
 * @since 2.1.0
 */
interface PoolInterface
{
    /**
     * Retrieve modifiers
     *
     * @return array
     * @since 2.1.0
     */
    public function getModifiers();

    /**
     * Retrieve modifiers instantiated
     *
     * @return ModifierInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.1.0
     */
    public function getModifiersInstances();
}
