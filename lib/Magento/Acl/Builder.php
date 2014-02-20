<?php
/**
 * Access Control List Builder. Retrieves required role/rule/resource loaders
 * and uses them to populate provided ACL object. Acl object is put to cache after creation.
 * On consequent requests, ACL object is deserialized from cache.
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Acl;

class Builder
{
    /**
     * Acl object
     *
     * @var \Magento\Acl
     */
    protected $_acl;

    /**
     * Acl loader list
     *
     * @var \Magento\Acl\LoaderInterface[]
     */
    protected $_loaderPool;

    /**
     * ACL cache
     *
     * @var \Magento\Acl\CacheInterface
     */
    protected $_cache;

    /**
     * @param \Magento\AclFactory $aclFactory
     * @param \Magento\Acl\CacheInterface $cache
     * @param \Magento\Acl\LoaderInterface $roleLoader
     * @param \Magento\Acl\LoaderInterface $resourceLoader
     * @param \Magento\Acl\LoaderInterface $ruleLoader
     */
    public function __construct(
        \Magento\AclFactory $aclFactory,
        \Magento\Acl\CacheInterface $cache,
        \Magento\Acl\LoaderInterface $roleLoader,
        \Magento\Acl\LoaderInterface $resourceLoader,
        \Magento\Acl\LoaderInterface $ruleLoader
    ) {
        $this->_aclFactory = $aclFactory;
        $this->_cache = $cache;
        $this->_loaderPool = array($roleLoader, $resourceLoader, $ruleLoader);
    }

    /**
     * Build Access Control List
     *
     * @return \Magento\Acl
     * @throws \LogicException
     */
    public function getAcl()
    {
        try {
            if ($this->_cache->has()) {
                $this->_acl = $this->_cache->get();
            } else {
                $this->_acl = $this->_aclFactory->create();
                foreach ($this->_loaderPool as $loader) {
                    $loader->populateAcl($this->_acl);
                }
                $this->_cache->save($this->_acl);
            }
        } catch (\Exception $e) {
            throw new \LogicException('Could not create acl object: ' . $e->getMessage());
        }

        return $this->_acl;
    }
}
