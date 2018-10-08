<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Serialize\Serializer;

/**
 * Class for processing of serialized form data.
 */
class FormData
{
    /**
     * Provides form data from the serialized data.
     *
     * @param string $serializedData
     * @return array
     * @throws \InvalidArgumentException
     */
    public function unserialize(string $serializedData): array
    {
        $formData = [];
        parse_str($serializedData, $formData);

        return $formData;
    }
}
