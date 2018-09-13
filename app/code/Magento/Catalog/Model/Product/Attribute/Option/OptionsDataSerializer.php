<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute\Option;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Attribute options data serializer.
 */
class OptionsDataSerializer
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Provides attribute options data from the serialized data.
     *
     * @param string $serializedOptions
     * @return array
     */
    public function unserialize(string $serializedOptions): array
    {
        $optionsData = [];
        $encodedOptions = $this->serializer->unserialize($serializedOptions);

        foreach ($encodedOptions as $encodedOption) {
            $decodedOptionData = [];
            parse_str($encodedOption, $decodedOptionData);
            $optionsData = array_replace_recursive($optionsData, $decodedOptionData);
        }

        return $optionsData;
    }
}
