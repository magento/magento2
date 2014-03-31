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
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

/**
 * Class Row reindex action
 * @package Magento\Catalog\Model\Indexer\Product\Flat\Action
 */
class Row extends \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
{
    /**
     * Execute row reindex action
     *
     * @param int|null $id
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\Action\Row
     * @throws \Magento\Model\Exception
     */
    public function execute($id = null)
    {
        if (!isset($id) || empty($id)) {
            throw new \Magento\Model\Exception(__('Could not rebuild index for undefined product'));
        }
        $ids = array($id);
        foreach ($this->_storeManager->getStores() as $store) {
            $this->_removeDeletedProducts($ids, $store->getId());
            if (isset($ids[0])) {
                $this->_reindexSingleProduct($store->getId(), $ids[0]);
            }
        }
        return $this;
    }
}
