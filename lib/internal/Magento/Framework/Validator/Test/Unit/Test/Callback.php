<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
