<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\TestDtoClasses;

class TestDto
{
    /**
     * @var string
     */
    private $paramOne;

    /**
     * @var string
     */
    private $paramTwo;

    /**
     * @var string
     */
    private $paramThree;

    /**
     * @param string $paramOne
     * @param string $paramTwo
     * @param string $paramThree
     */
    public function __construct(
        string $paramOne,
        string $paramTwo,
        string $paramThree
    ) {
        $this->paramOne = $paramOne;
        $this->paramTwo = $paramTwo;
        $this->paramThree = $paramThree;
    }

    /**
     * @return string
     */
    public function getParamOne(): string
    {
        return $this->paramOne;
    }

    /**
     * @return string
     */
    public function getParamTwo(): string
    {
        return $this->paramTwo;
    }

    /**
     * @return string
     */
    public function getParamThree(): string
    {
        return $this->paramThree;
    }
}
