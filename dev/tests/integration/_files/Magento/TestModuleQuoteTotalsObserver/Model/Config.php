<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleQuoteTotalsObserver\Model;

class Config
{
    private $active = false;

    public function enableObserver()
    {
        $this->active = true;
    }

    public function disableObserver()
    {
        $this->active = false;
    }

    public function isActive()
    {
        return $this->active;
    }
}
