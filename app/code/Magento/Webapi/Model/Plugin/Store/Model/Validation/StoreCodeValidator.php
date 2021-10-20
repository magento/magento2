<?php
declare(strict_types=1);

namespace Magento\Webapi\Model\Plugin\Store\Model\Validation;

use Magento\Store\Model\Validation\StoreCodeValidator as Subject;

/**
 * Validates that parsed store code is not a part of api type prefix.
 */
class StoreCodeValidator
{
    /**
     * @var string
     */
    private string $invalidStoreCode;

    /**
     * @param string $invalidStoreCode
     */
    public function __construct(string $invalidStoreCode)
    {
        $this->invalidStoreCode = $invalidStoreCode;
    }

    /**
     * @param Subject $subject
     * @param bool $result
     * @param string $value
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsValid(Subject $subject, bool $result, string $value): bool
    {
        return $result && $value !== $this->invalidStoreCode;
    }
}
