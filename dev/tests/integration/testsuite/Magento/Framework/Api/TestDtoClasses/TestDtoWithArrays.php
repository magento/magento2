<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\TestDtoClasses;

class TestDtoWithArrays
{
    /**
     * @var array|int[]
     */
    private $paramOne;

    /**
     * @var array
     */
    private $paramTwo;

    /**
     * @param int[] $paramOne
     * @param array $paramTwo
     */
    public function __construct(
        array $paramOne,
        array $paramTwo
    ) {
        $this->paramOne = $paramOne;
        $this->paramTwo = $paramTwo;
    }

    /**
     * @return int[]
     */
    public function getParamOne(): array
    {
        return $this->paramOne;
    }

    /**
     * @return array
     */
    public function getParamTwo(): array
    {
        return $this->paramTwo;
    }
}
