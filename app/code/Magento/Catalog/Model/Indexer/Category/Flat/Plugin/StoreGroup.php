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
namespace Magento\Catalog\Model\Indexer\Category\Flat\Plugin;

class StoreGroup
{
    /**
     * @var \Magento\Indexer\Model\IndexerInterface
     */
    protected $indexer;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $state;

    /**
     * @param \Magento\Indexer\Model\IndexerInterface $indexer
     * @param \Magento\Catalog\Model\Indexer\Category\Flat\State $state
     */
    public function __construct(
        \Magento\Indexer\Model\IndexerInterface $indexer,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $state
    ) {
        $this->indexer = $indexer;
        $this->state = $state;
    }

    /**
     * Return own indexer object
     *
     * @return \Magento\Indexer\Model\IndexerInterface
     */
    protected function getIndexer()
    {
        if (!$this->indexer->getId()) {
            $this->indexer->load(\Magento\Catalog\Model\Indexer\Category\Flat\State::INDEXER_ID);
        }
        return $this->indexer;
    }

    /**
     * Validate changes for invalidating indexer
     *
     * @param \Magento\Framework\Model\AbstractModel $group
     * @return bool
     */
    protected function validate(\Magento\Framework\Model\AbstractModel $group)
    {
        return $group->dataHasChangedFor('root_category_id') && !$group->isObjectNew();
    }

    /**
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $subject
     * @param callable $proceed
     * @param \Magento\Framework\Model\AbstractModel $group
     *
     * @return \Magento\Framework\Model\Resource\Db\AbstractDb
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        \Magento\Framework\Model\Resource\Db\AbstractDb $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $group
    ) {
        $needInvalidating = $this->validate($group);
        $objectResource = $proceed($group);
        if ($needInvalidating && $this->state->isFlatEnabled()) {
            $this->getIndexer()->invalidate();
        }

        return $objectResource;
    }
}
