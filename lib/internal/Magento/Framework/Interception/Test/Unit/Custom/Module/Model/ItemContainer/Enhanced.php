<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer;

use Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer;

class Enhanced extends ItemContainer
{
    /**
     * @return string
     */
    public function getName()
    {
        return parent::getName() . '_enhanced';
    }
}
