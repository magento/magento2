<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\ResourceModel\Grid;

class Collection extends \Magento\CatalogRule\Model\ResourceModel\Rule\Collection
{
    /**
     * @var \Magento\Framework\Model\Entity\MetadataPool
     */
    protected $metadataPool;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Model\Entity\MetadataPool $metadataPool
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\Entity\MetadataPool $metadataPool,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->metadataPool = $metadataPool;
    }

    /**
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addWebsitesToResult();

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _afterLoad()
    {
        /** @var \Magento\CatalogRule\Model\ResourceModel\Rule $resource */
        $resource = $this->getResource();
        $linkField = $this->metadataPool->getMetadata('Magento\CatalogRule\Api\Data\RuleInterface')->getLinkField();
        if ($this->getFlag('add_websites_to_result') && $this->_items) {
            /** @var \Magento\Rule\Model\AbstractModel $item */
            foreach ($this->_items as $item) {
                $item->setWebsiteIds($resource->getWebsiteIds($item->getData($linkField)));
            }
        }
        $this->setFlag('add_websites_to_result', false);

        return parent::_afterLoad();
    }
}
