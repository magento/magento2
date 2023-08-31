<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\DataProvider\Modifier;

use Magento\Framework\Exception\LocalizedException;

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
     * @throws LocalizedException
     */
    public function getModifiersInstances();
}
