<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session\SaveHandler;

/**
 * Php native session save handler
 * @since 2.0.0
 */
class Native extends \SessionHandler
{
    /**
     * Workaround for php7 session_regenerate_id error
     * @see https://bugs.php.net/bug.php?id=71187
     *
     * @param string $sessionId
     * @return string
     * @since 2.1.0
     */
    public function read($sessionId)
    {
        return (string)parent::read($sessionId);
    }
}
