<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail;

use Magento\Framework\Mail\Exception\InvalidArgumentException;

/**
 * Convert and filter email addresses
 */
class AddressConverter
{
    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * AddressConverter constructor
     *
     * @param AddressFactory $addressFactory
     */
    public function __construct(
        AddressFactory $addressFactory
    ) {
        $this->addressFactory = $addressFactory;
    }

    /**
     * Creates MailAddress from string values
     *
     * @param string $email
     * @param string|null $name
     *
     * @return Address
     */
    public function convert(string $email, ?string $name = null): Address
    {
        $email = $this->convertEmailUserToAscii($email);
        return $this->addressFactory->create(
            [
                'name' => $name,
                'email' => $email
            ]
        );
    }

    /**
     * Convert email user to ascii
     *
     * @param string $email
     * @return string
     */
    private function convertEmailUserToAscii(string $email): string
    {
        if (preg_match('/^(.+)@([^@]+)$/', $email, $matches)) {
            $user = $matches[1];
            $hostname = $matches[2];
            $userEncoded = idn_to_ascii($user, IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46, $idnaInfo);
            if ($userEncoded == $user) {
                return $email;
            }

            if ($userEncoded === false && array_key_exists('result', $idnaInfo)) {
                $userEncoded = $idnaInfo['result'];
            }
            $email = sprintf('%s@%s', $userEncoded, $hostname);
        }
        return $email;
    }

    /**
     * Converts array to list of MailAddresses
     *
     * @param array $addresses
     *
     * @return Address[]
     * @throws InvalidArgumentException
     */
    public function convertMany(array $addresses): array
    {
        $addressList = [];
        foreach ($addresses as $key => $value) {

            if (is_int($key) || is_numeric($key)) {
                $addressList[] = $this->convert($value);
                continue;
            }

            if (!is_string($key)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Invalid key type in provided addresses array ("%s")',
                        (is_object($key) ? get_class($key) : var_export($key, 1))
                    )
                );
            }
            $addressList[] = $this->convert($key, $value);
        }

        return $addressList;
    }
}
