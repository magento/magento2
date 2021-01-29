<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Message\Test\Unit;

use Magento\Framework\Message\MessageInterface;

/**
 * \Magento\Framework\Message\Notice test case
 */
class NoticeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Message\Notice
     */
    protected $model;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(\Magento\Framework\Message\Notice::class);
    }

    public function testGetType()
    {
        $this->assertEquals(MessageInterface::TYPE_NOTICE, $this->model->getType());
    }
}
