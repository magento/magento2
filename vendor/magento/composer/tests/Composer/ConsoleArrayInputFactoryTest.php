<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Composer\ConsoleArrayInputFactory;

class ConsoleArrayInputFactoryTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var ConsoleArrayInputFactory
     */
    protected $factory;

    protected function setUp()
    {
        $this->factory = new ConsoleArrayInputFactory();
    }

    public function testCreate()
    {
        $this->assertInstanceOf('\Symfony\Component\Console\Input\ArrayInput', $this->factory->create([]));
    }
}
