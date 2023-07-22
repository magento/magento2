<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\User\ViewModel;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * JsonSerializer
 */
class JsonSerializer implements ArgumentInterface
{
    /**
     * @param Json $serializer
     */
    public function __construct(
        private readonly Json $serializer
    ) {
    }

    /**
     * Returns serialized version of data
     *
     * @param array $data
     * @return string
     */
    public function serialize(array $data): string
    {
        return $this->serializer->serialize($data);
    }
}
