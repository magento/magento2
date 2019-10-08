<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetGraphQl\Model;

use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderInterface;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * SetPaymentMethod additional data provider model for Authorizenet payment method
 */
class AuthorizenetDataProvider implements AdditionalDataProviderInterface
{
    private const PATH_ADDITIONAL_DATA = 'authorizenet_acceptjs';

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    /**
     * Return additional data
     *
     * @param array $data
     * @return array
     */
    public function getData(array $data): array
    {
        $additionalData = $this->arrayManager->get(static::PATH_ADDITIONAL_DATA, $data) ?? [];
        foreach ($additionalData as $key => $value) {
            $additionalData[$this->convertSnakeCaseToCamelCase($key)] = $value;
            unset($additionalData[$key]);
        }
        return $additionalData;
    }

    /**
     * Convert an input string from snake_case to camelCase.
     *
     * @param string $input
     * @return string
     */
    private function convertSnakeCaseToCamelCase($input): string
    {
        return lcfirst(str_replace('_', '', ucwords($input, '_')));
    }
}
