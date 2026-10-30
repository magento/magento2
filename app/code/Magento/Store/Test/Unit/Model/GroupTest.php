<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Group;
use PHPUnit\Framework\TestCase;

class GroupTest extends TestCase
{
    /**
     * @var Group
     */
    protected $model;

    /**
     * @var ObjectManager
     */
    protected $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);

        $this->model = $this->objectManagerHelper->getObject(
            Group::class
        );
    }

    public function testGetCacheTags()
    {
        $this->assertEquals([Group::CACHE_TAG], $this->model->getCacheTags());
    }
}
