<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Cache\Tag\Strategy;

use \Magento\Framework\App\Cache\Tag\Strategy\Dummy;

class DummyTest extends \PHPUnit_Framework_TestCase
{

    private $model;

    protected function setUp()
    {
        $this->model = new Dummy();
    }

    public function testGetTagsWithScalar()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Provided argument is not an object');
        $this->model->getTags('scalar');
    }

    public function testGetTagsWithObject()
    {
        $emptyArray = [];

        $this->assertEquals($emptyArray, $this->model->getTags(new \StdClass));

        $identityInterface = $this->getMockForAbstractClass(\Magento\Framework\DataObject\IdentityInterface::class);
        $this->assertEquals($emptyArray, $this->model->getTags($identityInterface));
    }
}
