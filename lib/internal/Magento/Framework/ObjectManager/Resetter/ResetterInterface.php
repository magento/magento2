<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Resetter;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Interface that keeps track of the instances that need to be reset, and resets them
 */
interface ResetterInterface extends ResetAfterRequestInterface
{
    /**
     * Adds instance
     *
     * @param object $instance
     * @return void
     */
    public function addInstance(object $instance) : void;

    /**
     * Sets object manager
     *
     * @param ObjectManagerInterface $objectManager
     * @return void
     */
    public function setObjectManager(ObjectManagerInterface $objectManager) : void;
}
