<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Cache\Tag\Strategy;

use \Magento\Framework\App\Cache\Tag\Strategy\Dummy;

class DummyTest extends \PHPUnit_Framework_TestCase
{
    public function testGetTags()
    {
        $model = new Dummy();
        $emptyArray = [];

        $this->assertEquals($emptyArray, $model->getTags('scalar'));

        $this->assertEquals($emptyArray, $model->getTags(new \StdClass));

        $identityInterface = $this->getMockForAbstractClass(\Magento\Framework\DataObject\IdentityInterface::class);
        $this->assertEquals($emptyArray, $model->getTags($identityInterface));
    }
}
