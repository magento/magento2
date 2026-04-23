<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator;

class Item
{
    /**
     * @return string
     */
    public function getItem()
    {
        return 'item';
    }
}
