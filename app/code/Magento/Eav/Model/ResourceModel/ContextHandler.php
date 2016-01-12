<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\ResourceModel;

use Magento\Framework\Model\Entity\EntityMetadata;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ContextHandler
 */
class ContextHandler implements \Magento\Framework\Model\Operation\ContextHandlerInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ContextHandlerInterface constructor.
     *
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieve(EntityMetadata $metadata, array $entityData)
    {
        $contextFields = $metadata->getEntityContext();
        $context = [];
        if (isset($contextFields[\Magento\Store\Model\Store::STORE_ID])) {
            $context[\Magento\Store\Model\Store::STORE_ID] = $this->addStoreIdContext(
                $entityData,
                \Magento\Store\Model\Store::STORE_ID
            );
        }
        return $context;
    }

    /**
     * Add store_id filter to context from object data or store manager
     *
     * @param array $data
     * @param string $field
     * @return array
     */
    protected function addStoreIdContext(array $data, $field)
    {
        if (isset($data[$field])) {
            $storeId = $data[$field];
        } else {
            $storeId = (int)$this->storeManager->getStore(true)->getId();
        }
        $storeIds = [\Magento\Store\Model\Store::DEFAULT_STORE_ID];
        if ($storeId != \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
            $storeIds[] = $storeId;
        }

        return $storeIds;
    }
}
