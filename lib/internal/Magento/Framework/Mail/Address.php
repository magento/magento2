<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail;

/**
 * Class MailAddress
 */
class Address
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string
     */
    private $email;

    /**
     * MailAddress constructor
     *
     * @param string|null $email
     * @param string|null $name
     */
    public function __construct(
        ?string $email,
        ?string $name
    ) {
        $this->email = $email;
        $this->name = $name;
    }

    /**
     * Name getter
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Email getter
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }
}
