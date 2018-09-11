<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute\Option;

use Magento\Framework\App\RequestInterface;

/**
 * Attribute options data resolver.
 */
class OptionsDataResolver
{
    /**
     * Provides attribute options data from the request.
     *
     * @param RequestInterface $request
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getOptionsData(RequestInterface $request): array
    {
        $serializedOptions = $request->getParam('serialized_options');
        $optionsData = [];

        if ($serializedOptions) {
            $encodedOptions = json_decode($serializedOptions, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Unable to unserialize options data.');
            }

            foreach ($encodedOptions as $encodedOption) {
                $decodedOptionData = [];
                parse_str($encodedOption, $decodedOptionData);
                $optionsData = array_replace_recursive($optionsData, $decodedOptionData);
            }
        }

        return $optionsData;
    }
}
