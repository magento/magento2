<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Serialize\Serializer;

<<<<<<< HEAD
use Magento\Framework\Serialize\Serializer\Json;

=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
/**
 * Class for processing of serialized form data.
 */
class FormData
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param Json $serializer
     */
    public function __construct(Json $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Provides form data from the serialized data.
     *
     * @param string $serializedData
     * @return array
     * @throws \InvalidArgumentException
     */
    public function unserialize(string $serializedData): array
    {
        $encodedFields = $this->serializer->unserialize($serializedData);

        if (!is_array($encodedFields)) {
            throw new \InvalidArgumentException('Unable to unserialize value.');
        }

        $formData = [];
        foreach ($encodedFields as $item) {
            $decodedFieldData = [];
            parse_str($item, $decodedFieldData);
            $formData = array_replace_recursive($formData, $decodedFieldData);
        }

        return $formData;
    }
}
