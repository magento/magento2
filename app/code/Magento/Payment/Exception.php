<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment;

/**
 * Payment exception
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Exception extends \Exception
{
    /**
     * @var int|null
     */
    protected $_code = null;

    /**
     * @param string|null $message
     * @param int $code
     */
    public function __construct($message = null, $code = 0)
    {
        $this->_code = $code;
        parent::__construct($message, 0);
    }

    /**
     * @return int|null
     */
    public function getFields()
    {
        return $this->_code;
    }
}
