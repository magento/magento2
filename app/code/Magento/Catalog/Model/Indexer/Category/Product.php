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
namespace Magento\Catalog\Model\Indexer\Category;

class Product implements \Magento\Indexer\Model\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * Indexer ID in configuration
     */
    const INDEXER_ID = 'catalog_category_product';

    /**
     * @var Product\Action\FullFactory
     */
    protected $fullActionFactory;

    /**
     * @var Product\Action\RowsFactory
     */
    protected $rowsActionFactory;

    /**
     * @var \Magento\Indexer\Model\IndexerInterface
     */
    protected $indexer;

    /**
     * @param Product\Action\FullFactory $fullActionFactory
     * @param Product\Action\RowsFactory $rowsActionFactory
     * @param \Magento\Indexer\Model\IndexerInterface $indexer
     */
    public function __construct(
        Product\Action\FullFactory $fullActionFactory,
        Product\Action\RowsFactory $rowsActionFactory,
        \Magento\Indexer\Model\IndexerInterface $indexer
    ) {
        $this->fullActionFactory = $fullActionFactory;
        $this->rowsActionFactory = $rowsActionFactory;
        $this->indexer = $indexer;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     */
    public function execute($ids)
    {
        $this->executeAction($ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->fullActionFactory->create()->execute();
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList($ids)
    {
        $this->executeAction($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->executeAction(array($id));
    }

    /**
     * Execute action for single entity or list of entities
     *
     * @param int[] $ids
     * @return $this
     */
    protected function executeAction($ids)
    {
        $ids = array_unique($ids);
        $this->indexer->load(static::INDEXER_ID);

        /** @var Product\Action\Rows $action */
        $action = $this->rowsActionFactory->create();
        if ($this->indexer->isWorking()) {
            $action->execute($ids, true);
        }
        $action->execute($ids);

        return $this;
    }
}
