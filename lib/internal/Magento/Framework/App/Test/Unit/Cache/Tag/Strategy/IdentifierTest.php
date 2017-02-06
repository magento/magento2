<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Cache\Tag\Strategy;

use \Magento\Framework\App\Cache\Tag\Strategy\Identifier;

class IdentifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Identifier
     */
    private $model;

    protected function setUp()
    {
        $this->model = new Identifier;
    }

    public function testGetWithScalar()
    {
        $this->setExpectedException(\InvalidArgumentException::class, 'Provided argument is not an object');
        $this->model->getTags('scalar');
    }

    public function testGetTagsWithObject()
    {
        $this->assertEquals([], $this->model->getTags(new \StdClass));
    }

    public function testGetTagsWithIdentityInterface()
    {
        $object = $this->getMockForAbstractClass(\Magento\Framework\DataObject\IdentityInterface::class);

        $identities = ['id1', 'id2'];

        $object->expects($this->once())
            ->method('getIdentities')
            ->willReturn($identities);

        $this->assertEquals($identities, $this->model->getTags($object));
    }
}
