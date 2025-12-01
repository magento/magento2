<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator\Test\Unit\Test;

/**
 * Class with callback for testing callbacks
 */
class Callback
{
    const ID = 3;

    /**
     * @return int
     */
    public function getId()
    {
        return self::ID;
    }

    /**
     * Fake method for testing callbacks
     */
    public function configureValidator()
    {
    }
}
