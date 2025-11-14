<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Helper;

use Magento\Catalog\Model\Product\Option;

/**
 * Test helper for Magento\Catalog\Model\Product\Option (used in Bundle tests)
 *
 * Extends Option to add custom methods for testing both Bundle and Catalog options
 */
class OptionTestHelper extends Option
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var array
     */
    private $testData = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        // Skip parent constructor to avoid dependencies
    }

    /**
     * Override getData for testing
     *
     * @param string|null $key
     * @param mixed $index
     * @return mixed
     */
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null, $index = null)
    {
        if ($key === null) {
            // Return test data when no key specified
            return $this->testData['getData_return'] ?? $this->data;
        }
        return $this->data[$key] ?? null;
    }

    /**
     * Set test data for getData() method
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function setTestData(string $key, $value): self
    {
        $this->testData[$key] = $value;
        return $this;
    }

    /**
     * Get required for testing
     *
     * @return mixed
     */
    public function getRequired()
    {
        return $this->data['required'] ?? false;
    }

    /**
     * Set required for testing
     *
     * @param mixed $required
     * @return self
     */
    public function setRequired($required): self
    {
        $this->data['required'] = $required;
        return $this;
    }

    /**
     * Check if multi selection for testing
     *
     * @return mixed
     */
    public function isMultiSelection()
    {
        return $this->data['is_multi_selection'] ?? false;
    }

    /**
     * Get value for testing
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->data['value'] ?? null;
    }

    /**
     * Set value for testing
     *
     * @param mixed $value
     * @return self
     */
    public function setValue($value): self
    {
        $this->data['value'] = $value;
        return $this;
    }

    /**
     * Set selections for testing
     *
     * @param array $selections
     * @return self
     */
    public function setSelections(array $selections): self
    {
        $this->data['selections'] = $selections;
        return $this;
    }
}
