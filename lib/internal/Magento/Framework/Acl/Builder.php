<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl;

/**
 * Access Control List Builder. Retrieves required role/rule/resource loaders
 * and uses them to populate provided ACL object. Acl object is put to cache after creation.
 * On consequent requests, ACL object is deserialized from cache.
 *
 * @api
 * @since 2.0.0
 */
class Builder
{
    /**
     * Acl object
     *
     * @var \Magento\Framework\Acl
     * @since 2.0.0
     */
    protected $_acl;

    /**
     * Acl loader list
     *
     * @var \Magento\Framework\Acl\LoaderInterface[]
     * @since 2.0.0
     */
    protected $_loaderPool;

    /**
     * @var \Magento\Framework\AclFactory
     * @since 2.0.0
     */
    protected $_aclFactory;

    /**
     * @param \Magento\Framework\AclFactory $aclFactory
     * @param \Magento\Framework\Acl\LoaderInterface $roleLoader
     * @param \Magento\Framework\Acl\LoaderInterface $resourceLoader
     * @param \Magento\Framework\Acl\LoaderInterface $ruleLoader
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\AclFactory $aclFactory,
        \Magento\Framework\Acl\LoaderInterface $roleLoader,
        \Magento\Framework\Acl\LoaderInterface $resourceLoader,
        \Magento\Framework\Acl\LoaderInterface $ruleLoader
    ) {
        $this->_aclFactory = $aclFactory;
        $this->_loaderPool = [$roleLoader, $resourceLoader, $ruleLoader];
    }

    /**
     * Build Access Control List
     *
     * @return \Magento\Framework\Acl
     * @throws \LogicException
     * @since 2.0.0
     */
    public function getAcl()
    {
        if ($this->_acl instanceof \Magento\Framework\Acl) {
            return $this->_acl;
        }

        try {
            $this->_acl = $this->_aclFactory->create();
            foreach ($this->_loaderPool as $loader) {
                $loader->populateAcl($this->_acl);
            }
        } catch (\Exception $e) {
            throw new \LogicException('Could not create an acl object: ' . $e->getMessage());
        }

        return $this->_acl;
    }

    /**
     * Remove cached ACL instance.
     *
     * @return $this
     * @since 2.2.0
     */
    public function resetRuntimeAcl()
    {
        $this->_acl = null;
        return $this;
    }
}
