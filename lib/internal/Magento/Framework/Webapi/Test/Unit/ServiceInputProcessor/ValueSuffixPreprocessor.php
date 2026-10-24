<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit\ServiceInputProcessor;

use Magento\Framework\Webapi\CustomAttribute\PreprocessorInterface;

/**
 * Appends a suffix to the attribute value, filling in a missing value, and records the codes it was called with
 */
class ValueSuffixPreprocessor implements PreprocessorInterface
{
    /**
     * @var string[]
     */
    private $processedCodes = [];

    /**
     * @param string $attributeCode
     */
    public function __construct(
        private readonly string $attributeCode
    ) {
    }

    /**
     * @inheritdoc
     */
    public function shouldBeProcessed(string $key, $attribute): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function process(string $key, &$attribute)
    {
        $this->processedCodes[] = $key;
        $attribute['value'] = ($attribute['value'] ?? 'filled') . '-processed';
    }

    /**
     * @inheritdoc
     */
    public function getAffectedAttributes(): array
    {
        return [$this->attributeCode];
    }

    /**
     * Get the attribute codes passed to process()
     *
     * @return string[]
     */
    public function getProcessedCodes(): array
    {
        return $this->processedCodes;
    }
}
