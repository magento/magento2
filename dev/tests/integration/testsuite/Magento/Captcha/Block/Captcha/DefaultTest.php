<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Block\Captcha;

class DefaultTest extends \PHPUnit\Framework\TestCase
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
            \Magento\Captcha\Block\Captcha\DefaultCaptcha::class
        );
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testGetRefreshUrlWhenFrontendStore()
    {
        $this->assertStringContainsString('captcha/refresh', $this->_block->getRefreshUrl());
    }
}
