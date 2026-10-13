<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block;

use Magento\Customer\Block\Newsletter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NewsletterTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $urlBuilder;

    /**
     * @var Newsletter
     */
    protected $block;

    protected function setUp(): void
    {
        $this->urlBuilder = $this->createMock(UrlInterface::class);
        $helper = new ObjectManager($this);
        $this->block = $helper->getObject(
            Newsletter::class,
            ['urlBuilder' => $this->urlBuilder]
        );
    }

    public function testGetAction()
    {
        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('newsletter/manage/save', [])
            ->willReturn('newsletter/manage/save');

        $this->assertEquals('newsletter/manage/save', $this->block->getAction());
    }
}
