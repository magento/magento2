<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Message\Test\Unit;

use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Message\Notice;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * \Magento\Framework\Message\Notice test case
 */
class NoticeTest extends TestCase
{
    /**
     * @var Notice
     */
    protected $model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(Notice::class);
    }

    public function testGetType()
    {
        $this->assertEquals(MessageInterface::TYPE_NOTICE, $this->model->getType());
    }
}
