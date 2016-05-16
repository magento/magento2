<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

/**
 * Class EmailNotConfirmedException
 */
class EmailNotConfirmedException extends AuthenticationException
{
    /**
     * @deprecated
     */
    const EMAIL_NOT_CONFIRMED = 'Email not confirmed';
}
