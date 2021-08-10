<?php

namespace Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset;

/**
 * Test Class
 */
class PublicPrivateProtected
{
    const PROTECTED_VAR_NAME = 'bar';
    const PRIVATE_VAR_NAME = 'baz';

    /**
     * @var string
     */
    public $foo;

    /**
     * @var string
     */
    protected $bar;

    /**
     * @var string
     */
    private $baz;
}
