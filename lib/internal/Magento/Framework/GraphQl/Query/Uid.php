<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Encodes and decodes id and uid values
 */
class Uid
{
    /**
     * Decode UID value to ID
     *
     * @param string $uid
     * @return null|string
     * @phpcs:disable Magento2.Functions.DiscouragedFunction
     * @throws GraphQlInputException
     */
    public function decode(string $uid): ?string
    {
        if ($this->isValidBase64($uid)) {
            $result = base64_decode($uid, true);
            return ($result !== false) ? $result : null;
        }
        throw new GraphQlInputException(__('Value of uid "%1" is incorrect.', $uid));
    }

    /**
     * Encode ID value to UID
     *
     * @param string $id
     * @return string
     * @phpcs:disable Magento2.Functions.DiscouragedFunction
     */
    public function encode(string $id): string
    {
        return base64_encode($id);
    }

    /**
     * Validate base64 encoded value
     *
     * @param string $data
     * @return bool
     * @phpcs:disable Magento2.Functions.DiscouragedFunction
     */
    public function isValidBase64(string $data): bool
    {
        $decodedValue = base64_decode($data, true);
        if ($decodedValue === false) {
            return false;
        }

        return base64_encode($decodedValue) === $data;
    }
}
