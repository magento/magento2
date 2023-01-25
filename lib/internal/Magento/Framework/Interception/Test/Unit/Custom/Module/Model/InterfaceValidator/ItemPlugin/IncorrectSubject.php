<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
