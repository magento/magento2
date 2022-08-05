<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorization\Model\ResourceModel;

use Magento\Backend\App\AbstractAction;
use Magento\Framework\Acl\Builder;
use Magento\Framework\Acl\Data\CacheInterface;
use Magento\Framework\Acl\RootResource;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Psr\Log\LoggerInterface;

/**
 * Admin rule resource model
 */
class Rules extends AbstractDb
{
    /**
     * Root ACL resource
     *
     * @var RootResource
     */
    protected $_rootResource;

    /**
     * @var Builder
     */
    protected $_aclBuilder;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var CacheInterface
     */
    private $aclDataCache;

    /**
     * @param Context $context
     * @param Builder $aclBuilder
     * @param LoggerInterface $logger
     * @param RootResource $rootResource
     * @param CacheInterface $aclDataCache
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        Builder $aclBuilder,
        LoggerInterface $logger,
        RootResource $rootResource,
        CacheInterface $aclDataCache,
        $connectionName = null
    ) {
        $this->_aclBuilder = $aclBuilder;
        parent::__construct($context, $connectionName);
        $this->_rootResource = $rootResource;
        $this->_logger = $logger;
        $this->aclDataCache = $aclDataCache;
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
     * @throws LocalizedException
     */
    public function saveRel(\Magento\Authorization\Model\Rules $rule)
    {
        $connection = $this->getConnection();
        try {
            $connection->beginTransaction();
            $roleId = $rule->getRoleId();

            $condition = ['role_id = ?' => (int)$roleId];

            $connection->delete($this->getMainTable(), $condition);

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
                    $insertData = $this->_prepareDataForTable(
                        new \Magento\Framework\DataObject($row),
                        $this->getMainTable()
                    );

                    $connection->insert($this->getMainTable(), $insertData);
                } else {
                    /** Give basic admin permissions to any admin */
                    $postedResources[] = AbstractAction::ADMIN_RESOURCE;
                    $acl = $this->_aclBuilder->getAcl();
                    /** @var $resource \Magento\Framework\Acl\AclResource */
                    foreach ($acl->getResources() as $resourceId) {
                        $row['permission'] = in_array($resourceId, $postedResources) ? 'allow' : 'deny';
                        $row['resource_id'] = $resourceId;

                        $insertData = $this->_prepareDataForTable(
                            new \Magento\Framework\DataObject($row),
                            $this->getMainTable()
                        );
                        $connection->insert($this->getMainTable(), $insertData);
                    }
                }
            }

            $connection->commit();
            $this->aclDataCache->clean();
        } catch (LocalizedException $e) {
            $connection->rollBack();
            throw $e;
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->_logger->critical($e);
        }
    }
}
