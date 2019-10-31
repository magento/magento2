<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Code\Generator;

/**
 * Class Sample for Mutator generation
 */
class SampleDto
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
     * @var int
     */
    private $paramThree;

    /**
     * @param string $paramOne
     * @param string $paramTwo
     * @param int $paramThree
     */
    public function __construct(
        string $paramOne,
        string $paramTwo,
        int $paramThree
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
     * @return int
     */
    public function getParamThree(): int
    {
        return $this->paramThree;
    }
}
