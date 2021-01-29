<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Message\Test\Unit;

use Magento\Framework\Message\MessageInterface;

/**
 * \Magento\Framework\Message\Warning test case
 */
class WarningTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Message\Warning
     */
    protected $model;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(\Magento\Framework\Message\Warning::class);
    }

    public function testGetType()
    {
        $this->assertEquals(MessageInterface::TYPE_WARNING, $this->model->getType());
    }
}
