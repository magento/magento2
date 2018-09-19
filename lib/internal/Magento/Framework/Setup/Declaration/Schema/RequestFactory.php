<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema;

use Zend\Di\Di;

/**
 * Request Factory.
 */
class RequestFactory
{
    /**
     * @var Di
     */
    private $zendDi;

    /**
     * @var string
     */
    private static $instanceName = Request::class;

    /**
     * RequestFactory constructor.
     *
     * @param Di $zendDi
     */
    public function __construct(Di $zendDi)
    {
        $this->zendDi = $zendDi;
    }

    /**
     * Create request object with requestOptions params.
     *
     * @param  array $requestOptions
     * @return Request
     */
    public function create(array $requestOptions = [])
    {
        return $this->zendDi->newInstance(
            self::$instanceName,
            ['request' => $requestOptions]
        );
    }
}
