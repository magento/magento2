<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\TestAsset;

/**
 * Test asset used to test invalid argument types on the constructor invocation.
 */
class ConstructorWithTypeError
{
    /**
     * @var Basic
     */
    private $testArgument;

    /**
     * @param Basic $testArgument
     */
    public function __construct(Basic $testArgument)
    {
        $this->testArgument = $testArgument;
    }
}
