<?php
/**
 * Session config interface
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Session;

interface SaveHandlerInterface extends \Zend_Session_SaveHandler_Interface
{
    /**
     * Default session save handler
     */
    const DEFAULT_HANDLER = 'files';
}
