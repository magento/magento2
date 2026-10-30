<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Test\Unit\Fixture;

abstract class TSampleAbstract
{
    /**
     * @return array
     */
    public function getData()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getDataOverridden()
    {
        return [];
    }
}
