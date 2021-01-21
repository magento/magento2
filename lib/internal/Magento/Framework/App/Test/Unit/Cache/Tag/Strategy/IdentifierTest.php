<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Cache\Tag\Strategy;

use \Magento\Framework\App\Cache\Tag\Strategy\Identifier;

class IdentifierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Identifier
     */
    private $model;

    protected function setUp(): void
    {
        $this->model = new Identifier;
    }

    public function testGetWithScalar()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided argument is not an object');
        $this->model->getTags('scalar');
    }

    public function testGetTagsWithObject()
    {
        $this->assertEquals([], $this->model->getTags(new \stdClass));
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
