<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemPlugin;

use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item;

class IncorrectSubject
{
    /**
     * @param Item $subject
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetItem(Item $subject)
    {
        return true;
    }
}
