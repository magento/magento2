<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager;

interface DynamicConfigInterface
{
    /**
     * Map application initialization params to Object Manager configuration format
     *
     * @return array
     */
    public function getConfiguration();
}
