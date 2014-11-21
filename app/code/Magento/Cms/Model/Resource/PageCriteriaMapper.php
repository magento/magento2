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
namespace Magento\Cms\Model\Resource;

use Magento\Framework\DB\GenericMapper;

/**
 * Class PageCriteriaMapper
 */
class PageCriteriaMapper extends GenericMapper
{
    /**
     * @inheritdoc
     */
    protected function init()
    {
        $this->initResource('Magento\Cms\Model\Resource\Page');
        $this->map['fields']['store'] = 'store_table.store_id';
        $this->map['fields']['store_id'] = 'store_table.store_id';
    }

    /**
     * Set first store flag
     *
     * @param bool $flag
     * @return void
     */
    public function mapFirstStoreFlag($flag)
    {
        // do nothing since handled in collection afterLoad
    }

    /**
     * Add filter by store
     *
     * @param int|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return void
     */
    public function mapStoreFilter($store, $withAdmin)
    {
        $this->getSelect()->join(
            ['store_table' => $this->getTable('cms_page_store')],
            'main_table.page_id = store_table.page_id',
            []
        )->group('main_table.page_id');
        if (!is_array($store)) {
            if ($store instanceof \Magento\Store\Model\Store) {
                $store = [$store->getId()];
            } else {
                $store = [$store];
            }
        }
        if ($withAdmin) {
            $store[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }
        $field = $this->getMappedField('store');
        $this->select->where(
            $this->getConditionSql($field, ['in' => $store]),
            null,
            \Magento\Framework\DB\Select::TYPE_CONDITION
        );
    }
}
