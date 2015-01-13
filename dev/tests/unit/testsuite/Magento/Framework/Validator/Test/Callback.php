<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Validator\Test;

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
