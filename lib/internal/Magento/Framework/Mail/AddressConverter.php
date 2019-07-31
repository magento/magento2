<?php
/**
 * Copyright (c) 2019 TechDivision GmbH
 * All rights reserved
 *
 * This product includes proprietary software developed at TechDivision GmbH, Germany
 * For more information see https://www.techdivision.com/
 *
 * To obtain a valid license for using this software please contact us at
 * license@techdivision.com
 */
declare(strict_types=1);

namespace Magento\Framework\Mail;

use Magento\Framework\Exception\MailException;

/**
 * Class AddressConverter
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
        return $this->addressFactory->create(
            [
                'name' => $name,
                'email' => $email
            ]
        );
    }

    /**
     * Converts array to list of MailAddresses
     *
     * @param array $addresses
     *
     * @return Address[]
     * @throws MailException
     */
    public function convertMany(array $addresses): array
    {
        $addressList = [];
        foreach ($addresses as $key=>$value) {

            if (is_int($key) || is_numeric($key)) {
                $addressList[] = $this->convert($value);
                continue;
            }

            if (! is_string($key)) {
                throw new MailException(__(
                    'Invalid key type in provided addresses array ("%s")',
                    (is_object($key) ? get_class($key) : var_export($key, 1))
                ));
            }
            $addressList[] = $this->convert($key, $value);
        }

        return $addressList;
    }
}
