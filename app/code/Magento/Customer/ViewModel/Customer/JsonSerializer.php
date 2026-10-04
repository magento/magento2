<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\ViewModel\Customer;

use Magento\Framework\Serialize\Serializer\Json as Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Customer's json serializer view model
 */
class JsonSerializer implements ArgumentInterface
{
    /**
     * @param Json $jsonEncoder
     */
    public function __construct(
        private Json $jsonEncoder
    ) {
    }

    /**
     * Encode the mixed $value into the JSON format
     *
     * @param mixed $value
     * @return string
     */
    public function serialize(mixed $value): string
    {
        return $this->jsonEncoder->serialize($value);
    }
}
