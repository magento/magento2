<?php
/**
 * Default acl loader. Used as a fallback when no loaders were defined. Doesn't change ACL object passed.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\Loader;

/**
 * Class \Magento\Framework\Acl\Loader\DefaultLoader
 *
 * @since 2.0.0
 */
class DefaultLoader implements \Magento\Framework\Acl\LoaderInterface
{
    /**
     * Don't do anything to acl object.
     *
     * @param \Magento\Framework\Acl $acl
     * @return void
     * @since 2.0.0
     */
    public function populateAcl(\Magento\Framework\Acl $acl)
    {
        // Do nothing
    }
}
