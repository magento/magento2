<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail;

use Magento\Framework\Exception\MailException;
use Zend\Mail\Address;
use Zend\Mail\Exception\InvalidArgumentException;

/**
 * Class MailAddress
 */
class MailAddress extends Address
{
    /**
     * @inheritDoc
     *
     * @return MailAddress
     * @throws MailException
     */
    public static function fromString($address, $comment = null)
    {
        try {
            return parent::fromString($address, $comment);
        } catch (InvalidArgumentException $e) {
            throw new MailException(__($e->getMessage()));
        }
    }


}
