<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
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
