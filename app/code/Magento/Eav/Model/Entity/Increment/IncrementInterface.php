<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Increment;

/**
 * @api
 * @since 2.0.0
 */
interface IncrementInterface
{
    /**
     * Get next id
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getNextId();
}
