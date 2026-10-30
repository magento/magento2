<?php
/**
 * Session config interface
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Session;

/**
 * Interface \Magento\Framework\Session\SaveHandlerInterface
 *
 * @api
 */
interface SaveHandlerInterface extends \SessionHandlerInterface
{
    /**
     * Default session save handler
     */
    const DEFAULT_HANDLER = 'files';
}
