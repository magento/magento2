<?php
/**
 * Session config interface
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session;

interface SaveHandlerInterface extends \Zend_Session_SaveHandler_Interface
{
    /**
     * Default session save handler
     */
    const DEFAULT_HANDLER = 'files';
}
