<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorization\Model\Resource;

/**
 * Admin rule resource model
 */
class Rules extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Root ACL resource
     *
     * @var \Magento\Framework\Acl\RootResource
     */
    protected $_rootResource;

    /**
     * Acl object cache
     *
     * @var \Magento\Framework\Acl\CacheInterface
     */
    protected $_aclCache;

    /**
     * @var \Magento\Framework\Acl\Builder
     */
    protected $_aclBuilder;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Acl\Builder $aclBuilder
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Acl\RootResource $rootResource
     * @param \Magento\Framework\Acl\CacheInterface $aclCache
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\Acl\Builder $aclBuilder,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Acl\RootResource $rootResource,
        \Magento\Framework\Acl\CacheInterface $aclCache
    ) {
        $this->_aclBuilder = $aclBuilder;
        parent::__construct($resource);
        $this->_rootResource = $rootResource;
        $this->_aclCache = $aclCache;
        $this->_logger = $logger;
    }

    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('authorization_rule', 'rule_id');
    }

    /**
     * Save ACL resources
     *
     * @param \Magento\Authorization\Model\Rules $rule
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    public function saveRel(\Magento\Authorization\Model\Rules $rule)
    {
        try {
            $adapter = $this->_getWriteAdapter();
            $adapter->beginTransaction();
            $roleId = $rule->getRoleId();

            $condition = ['role_id = ?' => (int)$roleId];

            $adapter->delete($this->getMainTable(), $condition);

            $postedResources = $rule->getResources();
            if ($postedResources) {
                $row = [
                    'resource_id' => $this->_rootResource->getId(),
                    'privileges' => '', // not used yet
                    'role_id' => $roleId,
                    'permission' => 'allow',
                ];

                // If all was selected save it only and nothing else.
                if ($postedResources === [$this->_rootResource->getId()]) {
                    $insertData = $this->_prepareDataForTable(new \Magento\Framework\Object($row), $this->getMainTable());

                    $adapter->insert($this->getMainTable(), $insertData);
                } else {
                    $acl = $this->_aclBuilder->getAcl();
                    /** @var $resource \Magento\Framework\Acl\Resource */
                    foreach ($acl->getResources() as $resourceId) {
                        $row['permission'] = in_array($resourceId, $postedResources) ? 'allow' : 'deny';
                        $row['resource_id'] = $resourceId;

                        $insertData = $this->_prepareDataForTable(new \Magento\Framework\Object($row), $this->getMainTable());
                        $adapter->insert($this->getMainTable(), $insertData);
                    }
                }
            }

            $adapter->commit();
            $this->_aclCache->clean();
        } catch (\Magento\Framework\Model\Exception $e) {
            $adapter->rollBack();
            throw $e;
        } catch (\Exception $e) {
            $adapter->rollBack();
            $this->_logger->critical($e);
        }
    }
}
