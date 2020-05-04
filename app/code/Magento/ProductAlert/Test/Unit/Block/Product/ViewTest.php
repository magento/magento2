<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Test\Unit\Block\Product;

use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ProductAlert\Block\Product\View;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $block;

    /**
     * @var MockObject
     */
    protected $postHelper;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->postHelper = $this->createMock(PostHelper::class);
        $this->block = $objectManager->getObject(
            View::class,
            ['coreHelper' => $this->postHelper]
        );
    }

    public function testGetPostAction()
    {
        $this->block->setSignupUrl('someUrl');
        $this->postHelper->expects($this->once())
            ->method('getPostData')
            ->with('someUrl')
            ->willReturn('{parsedAction}');
        $this->assertEquals('{parsedAction}', $this->block->getPostAction());
    }
}
