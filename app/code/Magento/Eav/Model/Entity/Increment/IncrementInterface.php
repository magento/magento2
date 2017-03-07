<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Increment;

interface IncrementInterface
{
    /**
     * Get next id
     *
     * @return mixed
     */
    public function getNextId();
}
