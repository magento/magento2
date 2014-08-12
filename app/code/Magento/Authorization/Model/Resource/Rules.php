<?php
/**
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
     * @var \Magento\Framework\Logger
     */
    protected $_logger;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Acl\Builder $aclBuilder
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Acl\RootResource $rootResource
     * @param \Magento\Framework\Acl\CacheInterface $aclCache
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\Acl\Builder $aclBuilder,
        \Magento\Framework\Logger $logger,
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

            $condition = array('role_id = ?' => (int)$roleId);

            $adapter->delete($this->getMainTable(), $condition);

            $postedResources = $rule->getResources();
            if ($postedResources) {
                $row = array(
                    'resource_id' => $this->_rootResource->getId(),
                    'privileges' => '', // not used yet
                    'role_id' => $roleId,
                    'permission' => 'allow'
                );

                // If all was selected save it only and nothing else.
                if ($postedResources === array($this->_rootResource->getId())) {
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
            $this->_logger->logException($e);
        }
    }
}
