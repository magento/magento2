<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Message\Test\Unit;

use Magento\Framework\Message\Error;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * \Magento\Framework\Message\Error test case
 */
class ErrorTest extends TestCase
{
    /**
     * @var Error
     */
    protected $model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(Error::class);
    }

    public function testGetType()
    {
        $this->assertEquals(MessageInterface::TYPE_ERROR, $this->model->getType());
    }
}
