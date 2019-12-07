<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Controller\Adminhtml\Iframe;

/**
 * @magentoAppArea adminhtml
 */
class ShowTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Check Swatch Acl Access
     */
    public function testAclAccess()
    {
        /** @var $acl \Magento\Framework\Acl */
        $acl = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Acl\Builder::class)
            ->getAcl();

        $acl->allow(null, \Magento\Swatches\Controller\Adminhtml\Iframe\Show::ADMIN_RESOURCE);

        $this->dispatch('backend/swatches/iframe/show/');

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $this->assertNotContains('Sorry, you need permissions to view this content.', $this->getResponse()->getBody());
    }

    /**
     * Check Swatch Acl Access Denied
     */
    public function testAclAccessDenied()
    {
        /** @var $acl \Magento\Framework\Acl */
        $acl = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Acl\Builder::class)
            ->getAcl();

        $acl->deny(null, \Magento\Swatches\Controller\Adminhtml\Iframe\Show::ADMIN_RESOURCE);

        $this->dispatch('backend/swatches/iframe/show/');

        $this->assertEquals(403, $this->getResponse()->getHttpResponseCode());
        $this->assertContains('Sorry, you need permissions to view this content.', $this->getResponse()->getBody());
    }
}
