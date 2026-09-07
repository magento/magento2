<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Cache\Tag\Strategy;

use Magento\Framework\App\Cache\Tag\Strategy\Identifier;
use Magento\Framework\DataObject\IdentityInterface;
use PHPUnit\Framework\TestCase;

class IdentifierTest extends TestCase
{
    /**
     * @var Identifier
     */
    private $model;

    protected function setUp(): void
    {
        $this->model = new Identifier();
    }

    public function testGetWithScalar()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided argument is not an object');
        $this->model->getTags('scalar');
    }

    public function testGetTagsWithObject()
    {
        $this->assertEquals([], $this->model->getTags(new \stdClass()));
    }

    public function testGetTagsWithIdentityInterface()
    {
        $object = $this->createMock(IdentityInterface::class);

        $identities = ['id1', 'id2'];

        $object->expects($this->once())
            ->method('getIdentities')
            ->willReturn($identities);

        $this->assertEquals($identities, $this->model->getTags($object));
    }
}
