<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Command\Result;

use Magento\Payment\Gateway\Command\ResultInterface;

class ArrayResult implements ResultInterface
{
    /**
     * @var array
     */
    private $array;

    /**
     * @param array $array
     */
    public function __construct(array $array = [])
    {
        $this->array = $array;
    }

    /**
     * Returns result interpretation
     *
     * @return array
     */
    public function get()
    {
        return $this->array;
    }
}
