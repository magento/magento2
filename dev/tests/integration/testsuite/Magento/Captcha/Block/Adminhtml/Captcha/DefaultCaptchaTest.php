<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Captcha\Block\Adminhtml\Captcha;

class DefaultCaptchaTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Captcha\Block\Captcha\DefaultCaptcha
     */
    protected $_block;

    protected function setUp(): void
    {
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Captcha\Block\Adminhtml\Captcha\DefaultCaptcha::class
        );
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea adminhtml
     */
    public function testGetRefreshUrl()
    {
        $this->assertStringContainsString('backend/admin/refresh/refresh', $this->_block->getRefreshUrl());
    }
}
