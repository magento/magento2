<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\TestDtoClasses;

class TestHybridDto
{
    /**
     * @var string
     */
    private $immutableOne;

    /**
     * @var string
     */
    private $immutableTwo;

    /**
     * @var string
     */
    private $mutableThree;

    /**
     * @param string $immutableOne
     * @param string $immutableTwo
     */
    public function __construct(
        string $immutableOne,
        string $immutableTwo
    ) {
        $this->immutableOne = $immutableOne;
        $this->immutableTwo = $immutableTwo;
    }

    /**
     * @return string
     */
    public function getImmutableOne(): string
    {
        return $this->immutableOne;
    }

    /**
     * @return string
     */
    public function getImmutableTwo(): string
    {
        return $this->immutableTwo;
    }

    /**
     * @return string
     */
    public function getMutableThree(): string
    {
        return $this->mutableThree;
    }

    /**
     * @param string $mutableThree
     */
    public function setMutableThree(string $mutableThree): void
    {
        $this->mutableThree = $mutableThree;
    }


}
