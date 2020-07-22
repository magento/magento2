<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Cache\Tag\Strategy;

use Magento\Framework\App\Cache\Tag\Strategy\Dummy;
use Magento\Framework\DataObject\IdentityInterface;
use PHPUnit\Framework\TestCase;

class DummyTest extends TestCase
{
    private $model;

    protected function setUp(): void
    {
        $this->model = new Dummy();
    }

    public function testGetTagsWithScalar()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided argument is not an object');
        $this->model->getTags('scalar');
    }

    public function testGetTagsWithObject()
    {
        $emptyArray = [];

        $this->assertEquals($emptyArray, $this->model->getTags(new \stdClass()));

        $identityInterface = $this->getMockForAbstractClass(IdentityInterface::class);
        $this->assertEquals($emptyArray, $this->model->getTags($identityInterface));
    }
}
