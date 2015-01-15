<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Block\Captcha;

class DefaultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Captcha\Block\Captcha\DefaultCaptcha
     */
    protected $_block;

    protected function setUp()
    {
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Captcha\Block\Captcha\DefaultCaptcha'
        );
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testGetRefreshUrlWhenFrontendStore()
    {
        $this->assertContains('captcha/refresh', $this->_block->getRefreshUrl());
    }
}
