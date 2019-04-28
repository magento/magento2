<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\TestDtoClasses;

class TestDto2
{
    /**
     * @var int
     */
    private $param1;

    /**
     * @var float
     */
    private $param2;

    /**
     * @var string
     */
    private $param3;

    /**
     * @param int $param1
     * @param float $param2
     * @param string $param3
     */
    public function __construct(
        int $param1,
        float $param2,
        string $param3
    ) {
        $this->param1 = $param1;
        $this->param2 = $param2;
        $this->param3 = $param3;
    }

    /**
     * @return int
     */
    public function getParam1(): int
    {
        return $this->param1;
    }

    /**
     * @return float
     */
    public function getParam2(): float
    {
        return $this->param2;
    }

    /**
     * @return string
     */
    public function getParam3(): string
    {
        return $this->param3;
    }
}
