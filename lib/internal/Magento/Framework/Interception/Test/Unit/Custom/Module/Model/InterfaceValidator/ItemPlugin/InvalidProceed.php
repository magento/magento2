<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemPlugin;

use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\Item;

class InvalidProceed
{
    /**
     * @param Item $subject
     * @param string $name
     * @param string $surname
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetItem(
        Item $subject,
        $name,
        $surname
    ) {
        return $name . $surname;
    }
}
