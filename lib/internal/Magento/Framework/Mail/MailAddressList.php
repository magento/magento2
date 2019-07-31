<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail;

use Countable;
use Iterator;
use Magento\Framework\Exception\MailException;

/**
 * Class MailAddressList
 */
class MailAddressList implements Countable, Iterator
{
    /**
     * @var MailAddress[]
     */
    private $addresses;

    /**
     * @var MailAddressFactory
     */
    private $mailAddressFactory;

    /**
     * MailAddressList constructor
     *
     * @param MailAddressFactory $mailAddressFactory
     */
    public function __construct(
        MailAddressFactory $mailAddressFactory
    ) {
        $this->mailAddressFactory = $mailAddressFactory;
    }

    /**
     * Add an address to the list
     *
     * @param string|MailAddress $emailOrAddress
     * @param null|string $name
     *
     * @return MailAddressList
     * @throws MailException
     */
    public function add($emailOrAddress, ?string $name = null): MailAddressList
    {
        if (is_string($emailOrAddress)) {
            $emailOrAddress = $this->createAddress($emailOrAddress, $name);
        }

        if (! $emailOrAddress instanceof MailAddress) {
            throw new MailException(__(
                '%s expects an email address or %s\MailAddress object as its first argument; received "%s"',
                __METHOD__,
                __NAMESPACE__,
                (is_object($emailOrAddress) ? get_class($emailOrAddress) : gettype($emailOrAddress))
            ));
        }

        $email = strtolower($emailOrAddress->getEmail());
        if ($this->has($email)) {
            return $this;
        }

        $this->addresses[$email] = $emailOrAddress;
        return $this;
    }

    /**
     * Add many addresses at once
     *
     * @param array $addresses
     *
     * @return MailAddressList
     * @throws MailException
     */
    public function addMany(array $addresses): MailAddressList
    {
        foreach ($addresses as $key => $value) {
            if (is_int($key) || is_numeric($key)) {
                $this->add($value);
                continue;
            }

            if (! is_string($key)) {
                throw new MailException(__(
                    'Invalid key type in provided addresses array ("%s")',
                    (is_object($key) ? get_class($key) : var_export($key, 1))
                ));
            }

            $this->add($key, $value);
        }

        return $this;
    }

    /**
     * Merge another address list into this one
     *
     * @param MailAddressList $addressList
     *
     * @return MailAddressList
     * @throws MailException
     */
    public function merge(MailAddressList $addressList): MailAddressList
    {
        foreach ($addressList as $address) {
            $this->add($address);
        }
        return $this;
    }

    /**
     * Does the email exist in this list?
     *
     * @param string $email
     *
     * @return bool
     */
    public function has($email): bool
    {
        $email = strtolower($email);

        return isset($this->addresses[$email]);
    }

    /**
     * Delete an address from the list
     *
     * @param string $email
     *
     * @return bool
     */
    public function delete($email): bool
    {
        $email = strtolower($email);
        if (!isset($this->addresses[$email])) {
            return false;
        }

        unset($this->addresses[$email]);

        return true;
    }

    /**
     * Return count of addresses
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->addresses);
    }

    /**
     * Rewind iterator
     *
     * @return mixed
     */
    public function rewind()
    {
        return reset($this->addresses);
    }

    /**
     * Return current item in iteration
     *
     * @return MailAddress
     */
    public function current(): MailAddress
    {
        return current($this->addresses);
    }

    /**
     * Return key of current item of iteration
     *
     * @return string
     */
    public function key(): string
    {
        return key($this->addresses);
    }

    /**
     * Move to next item
     *
     * @return mixed
     */
    public function next()
    {
        return next($this->addresses);
    }

    /**
     * Is the current item of iteration valid?
     *
     * @return bool
     */
    public function valid(): bool
    {
        $key = key($this->addresses);

        return ($key !== null && $key !== false);
    }

    /**
     * Create an address object
     *
     * @param string $email
     * @param string|null $name
     *
     * @return MailAddress
     */
    private function createAddress(string $email, ?string $name=null): MailAddress
    {
        return $this->mailAddressFactory->create(['email' => $email, 'name' => $name]);
    }

    /**
     * Add an address to the list from any valid string format, such as
     *  - "ZF Dev" <dev@zf.com>
     *  - dev@zf.com
     *
     * @param string $address
     * @param null|string $comment Comment associated with the address, if any.
     * @return MailAddressList
     * @throws MailException
     */
    public function addFromString($address, $comment = null): MailAddressList
    {
        $this->add(MailAddress::fromString($address, $comment));

        return $this;
    }
}
