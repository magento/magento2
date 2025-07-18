<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Utility;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class IPAddress
{
    /**
     * Returns true if the supplied string is a valid IPv4 or IPv6 address
     *
     * @param string $address
     *
     * @return bool
     */
    public function isValidAddress(string $address): bool
    {
        return $address === filter_var($address, FILTER_VALIDATE_IP);
    }

    /**
     * Returns true if the supplied string is a valid IP address range in CIDR notation
     *
     * @param string $range
     *
     * @return bool
     */
    public function isValidRange(string $range): bool
    {
        if (!str_contains($range, '/')) {
            return false;
        }

        [$network, $mask] = explode('/', $range, 2);
        $maxLength = str_contains($network, ':') ? 128 : 32;

        if (!is_numeric($mask) || $mask < 0 || $mask > $maxLength) {
            return false;
        }

        return $this->isValidAddress($network);
    }

    /**
     * Returns true if the supplied address is included within the supplied range
     *
     * @param string $range
     * @param string $address
     *
     * @return bool
     *
     * @throws LocalizedException
     */
    public function rangeContainsAddress(string $range, string $address): bool
    {
        if (!$this->isValidRange($range)) {
            throw new LocalizedException(new Phrase('Invalid range provided: %1', [$range]));
        }

        if (!$this->isValidAddress($address)) {
            throw new LocalizedException(new Phrase('Invalid address provided: %1', [$address]));
        }

        [$network, $mask] = explode('/', $range, 2);
        $mask = (int) $mask;

        return substr($this->ipAddressToBinaryString($network), 0, $mask)
            === substr($this->ipAddressToBinaryString($address), 0, $mask);
    }

    /**
     * Returns the binary representation of an IP address as a string
     *
     * @param string $address
     *
     * @return string
     */
    private function ipAddressToBinaryString(string $address): string
    {
        $binary = '';
        foreach (unpack('C*', inet_pton($address)) as $byte) {
            $binary .= str_pad(decbin($byte), 8, '0', STR_PAD_LEFT);
        }

        return $binary;
    }
}
