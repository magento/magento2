<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Command\Result;

use Magento\Payment\Gateway\Command\ResultInterface;

/**
 * Container for boolean value that should be returned as command result.
 *
 * @api
 * @since 100.0.2
 */
class BoolResult implements ResultInterface
{
    /**
     * @var array
     */
    private $result;

    /**
     * Constructor
     *
     * @param bool $result
     */
    public function __construct($result = true)
    {
        $this->result = $result;
    }

    /**
     * Returns result interpretation
     *
     * @return mixed
     */
    public function get()
    {
        return (bool)$this->result;
    }
}
