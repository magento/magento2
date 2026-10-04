<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model;

use Magento\Framework\DataObject;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Storage for validated VAT data
 */
class VatValidationResultStorage extends DataObject implements ResetAfterRequestInterface
{
    /**
     * Store VAT validated data by key
     *
     * @param string|int $vatNumber
     * @param string $countryCode
     * @param DataObject $vatValidationResult
     * @return void
     */
    public function set(string|int $vatNumber, string $countryCode, DataObject $vatValidationResult): void
    {
        $this->setData($vatNumber . $countryCode, $vatValidationResult);
    }

    /**
     * Retrieve VAT validated data by key
     *
     * @param string|int $vatNumber
     * @param string $countryCode
     * @return DataObject|null
     */
    public function get(string|int $vatNumber, string $countryCode): ?DataObject
    {
        return $this->getData($vatNumber . $countryCode);
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->setData([]);
    }
}
