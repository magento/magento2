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
namespace Magento\CatalogRule\Model\Indexer;

use Magento\CatalogRule\CatalogRuleException;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Magento\Indexer\Model\ActionInterface as IndexerActionInterface;

abstract class AbstractIndexer implements IndexerActionInterface, MviewActionInterface
{
    /**
     * @var IndexBuilder
     */
    protected $indexBuilder;

    /**
     * @param IndexBuilder $indexBuilder
     */
    public function __construct(IndexBuilder $indexBuilder)
    {
        $this->indexBuilder = $indexBuilder;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     */
    public function execute($ids)
    {
        $this->executeList($ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->indexBuilder->reindexFull();
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @throws CatalogRuleException
     * @return void
     */
    public function executeList(array $ids)
    {
        if (!$ids) {
            throw new CatalogRuleException(__('Could not rebuild index for empty products array'));
        }
        $this->doExecuteList($ids);
    }

    /**
     * Execute partial indexation by ID list. Template method
     *
     * @param int[] $ids
     * @return void
     */
    abstract protected function doExecuteList($ids);

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @throws CatalogRuleException
     * @return void
     */
    public function executeRow($id)
    {
        if (!$id) {
            throw new CatalogRuleException(__('Could not rebuild index for undefined product'));
        }
        $this->doExecuteRow($id);
    }

    /**
     * Execute partial indexation by ID. Template method
     *
     * @param int $id
     * @throws \Magento\CatalogRule\CatalogRuleException
     * @return void
     */
    abstract protected function doExecuteRow($id);
}
