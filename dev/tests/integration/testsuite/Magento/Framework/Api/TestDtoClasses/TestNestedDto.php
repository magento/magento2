<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Api\TestDtoClasses;

class TestNestedDto
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var TestDto
     */
    private $testDto1;

    /**
     * @var TestDto
     */
    private $testDto2;

    /**
     * @var TestDto[]
     */
    private $testDtoArray;

    /**
     * @param string $id
     * @param TestDto $testDto1
     * @param TestDto $testDto2
     * @param TestDto[] $testDtoArray
     */
    public function __construct(
        string $id,
        TestDto $testDto1,
        TestDto $testDto2,
        array $testDtoArray
    ) {
        $this->id = $id;
        $this->testDto1 = $testDto1;
        $this->testDto2 = $testDto2;
        $this->testDtoArray = $testDtoArray;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return TestDto
     */
    public function getTestDto1(): TestDto
    {
        return $this->testDto1;
    }

    /**
     * @return TestDto
     */
    public function getTestDto2(): TestDto
    {
        return $this->testDto2;
    }

    /**
     * @return TestDto[]
     */
    public function getTestDtoArray(): array
    {
        return $this->testDtoArray;
    }
}
