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
namespace Magento\Catalog\Model\Indexer\Product\Flat\Action;

/**
 * Class Full reindex action
 *
 */
class Full extends \Magento\Catalog\Model\Indexer\Product\Flat\AbstractAction
{
    /**
     * Execute full reindex action
     *
     * @param null|array $ids
     *
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\Action\Full
     * @throws \Magento\Framework\Exception
     * @throws \Exception
     */
    public function execute($ids = null)
    {
        try {
            foreach ($this->_storeManager->getStores() as $store) {
                $this->_reindex($store->getId());
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Model\Exception($e->getMessage(), $e->getCode(), $e);
        }
        return $this;
    }
}
