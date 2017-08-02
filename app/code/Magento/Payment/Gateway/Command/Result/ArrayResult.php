<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Command\Result;

use Magento\Payment\Gateway\Command\ResultInterface;

/**
 * Container for array that should be returned as command result.
 *
 * @api
 * @since 2.0.0
 */
class ArrayResult implements ResultInterface
{
    /**
     * @var array
     * @since 2.0.0
     */
    private $array;

    /**
     * @param array $array
     * @since 2.0.0
     */
    public function __construct(array $array = [])
    {
        $this->array = $array;
    }

    /**
     * Returns result interpretation
     *
     * @return array
     * @since 2.0.0
     */
    public function get()
    {
        return $this->array;
    }
}
