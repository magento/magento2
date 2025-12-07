<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemPlugin;

use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\Item;

class IncompatibleArgumentsCount
{
    /**
     * @param Item $subject
     * @param string $name
     * @param string $surname
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetItem(
        Item $subject,
        $name,
        $surname
    ) {
        return $name . $surname;
    }
}
