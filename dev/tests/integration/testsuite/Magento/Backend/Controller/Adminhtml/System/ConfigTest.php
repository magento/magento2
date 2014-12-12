<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Controller\Adminhtml\System;

/**
 * @magentoAppArea adminhtml
 */
class ConfigTest extends \Magento\Backend\Utility\Controller
{
    public function testEditAction()
    {
        $this->dispatch('backend/admin/system_config/edit');
        $this->assertContains('<ul id="system_config_tabs"', $this->getResponse()->getBody());
    }
}
