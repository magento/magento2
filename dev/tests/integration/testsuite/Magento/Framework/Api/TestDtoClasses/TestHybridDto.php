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
     * @var string|null
     */
    private $mutableFour;

    /**
     * @var string|null
     */
    private $mutableFive;

    /**
     * @var string|null
     */
    private $mutableSix;

    /**
     * @param string $immutableOne
     * @param string $immutableTwo
     * @param string $mutableThree
     * @param string|null $mutableFour
     * @param string|null $mutableFive
     */
    public function __construct(
        string $immutableOne,
        string $immutableTwo,
        string $mutableThree,
        ?string $mutableFour,
        string $mutableFive = null
    ) {
        $this->immutableOne = $immutableOne;
        $this->immutableTwo = $immutableTwo;
        $this->mutableThree = $mutableThree;
        $this->mutableFour = $mutableFour;
        $this->mutableFive = $mutableFive;
    }

    /**
     * @return string|null
     */
    public function getMutableFive(): ?string
    {
        return $this->mutableFive;
    }

    /**
     * @param string|null $mutableFive
     */
    public function setMutableFive(?string $mutableFive): void
    {
        $this->mutableFive = $mutableFive;
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

    /**
     * @return string|null
     */
    public function getMutableFour(): ?string
    {
        return $this->mutableFour;
    }

    /**
     * @param string|null $mutableFour
     */
    public function setMutableFour(?string $mutableFour): void
    {
        $this->mutableFour = $mutableFour;
    }

    /**
     * @return string|null
     */
    public function getMutableSix(): ?string
    {
        return $this->mutableSix;
    }

    /**
     * @param string|null $mutableSix
     */
    public function setMutableSix(?string $mutableSix): void
    {
        $this->mutableSix = $mutableSix;
    }
}
