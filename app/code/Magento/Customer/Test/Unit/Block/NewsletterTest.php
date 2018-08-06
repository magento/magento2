<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block;

use Magento\Customer\Block\Newsletter;

class NewsletterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    /**
     * @var Newsletter
     */
    protected $block;

    protected function setUp()
    {
        $this->urlBuilder = $this->createMock(\Magento\Framework\UrlInterface::class);
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->block = $helper->getObject(
            \Magento\Customer\Block\Newsletter::class,
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
