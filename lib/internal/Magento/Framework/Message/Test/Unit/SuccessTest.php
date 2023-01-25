<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Message\Test\Unit;

use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Message\Success;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * \Magento\Framework\Message\Success test case
 */
class SuccessTest extends TestCase
{
    /**
     * @var Success
     */
    protected $model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(Success::class);
    }

    public function testGetType()
    {
        $this->assertEquals(MessageInterface::TYPE_SUCCESS, $this->model->getType());
    }
}
