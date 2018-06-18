<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2018 Atwix (https://www.atwix.com/)
 */


namespace Magento\Framework\ObjectManager\TestAsset;


class ConstructorWithThrowable extends \Magento\Framework\ObjectManager\TestAsset\ConstructorOneArgument
{
    public function __construct(Basic $one)
    {
        // Call parent constructor without parameters to generate TypeError
        parent::__construct();
    }
}