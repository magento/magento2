<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Paypal\etc;

use Magento\Framework\Acl\AclResource\ProviderInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class AclTest extends TestCase
{
    /**
     * Check to ensure that paypal is child of payment acl
     *
     * @return void
     */
    public function testAclInheritance()
    {
        $aclResourceProvider = Bootstrap::getObjectManager()->get(ProviderInterface::class);
        $resources = $aclResourceProvider->getAclResources();
        $admin = $this->getChildren($resources, 'Magento_Backend::admin');
        $stores = $this->getChildren($admin['children'], 'Magento_Backend::stores');
        $settings = $this->getChildren($stores['children'], 'Magento_Backend::stores_settings');
        $config = $this->getChildren($settings['children'], 'Magento_Config::config');
        $payment = $this->getChildren($config['children'], 'Magento_Payment::payment');
        $children = [];
        foreach ($payment['children'] as $child) {
            $children[] = $child['id'];
        }
        $this->assertContains('Magento_Paypal::paypal', $children);
    }

    /**
     * Filters specified children branch from acl resource
     *
     * @param array $aclResource
     * @param string $filter
     * @return array
     */
    private function getChildren(array $aclResource, string $filter) : array
    {
        $filtered = array_filter(
            $aclResource,
            function ($node) use ($filter) {
                return isset($node['id'])
                    && $node['id'] === $filter;
            }
        );
        return reset($filtered);
    }
}
