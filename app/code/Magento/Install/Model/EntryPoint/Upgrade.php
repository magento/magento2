<?php
/**
 * Entry point for upgrading application
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Install\Model\EntryPoint;

class Upgrade extends \Magento\Core\Model\AbstractEntryPoint
{
    /**
     * Key for passing reindexing parameter
     */
    const REINDEX = 'reindex';

    /**@#+
     * Reindexing modes
     */
    const REINDEX_INVALID = 1;
    const REINDEX_ALL = 2;
    /**@#-*/

    /**
     * Apply scheme & data updates
     */
    protected function _processRequest()
    {
        /** @var $cacheFrontendPool \Magento\Core\Model\Cache\Frontend\Pool */
        $cacheFrontendPool = $this->_objectManager->get('Magento\Core\Model\Cache\Frontend\Pool');
        /** @var $cacheFrontend \Magento\Cache\FrontendInterface */
        foreach ($cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->clean();
        }

        /** @var $updater \Magento\App\Updater */
        $updater = $this->_objectManager->get('Magento\App\Updater');
        $updater->updateScheme();
        $updater->updateData();

        $this->_reindex();
    }

    /**
     * Perform reindexing if requested
     */
    private function _reindex()
    {
        /** @var $config \Magento\Core\Model\Config\Primary */
        $config = $this->_objectManager->get('Magento\Core\Model\Config\Primary');
        $reindexMode = $config->getParam(self::REINDEX);
        if ($reindexMode) {
            /** @var $indexer \Magento\Index\Model\Indexer */
            $indexer = $this->_objectManager->get('Magento\Index\Model\Indexer');
            if (self::REINDEX_ALL == $reindexMode) {
                $indexer->reindexAll();
            } elseif (self::REINDEX_INVALID == $reindexMode) {
                $indexer->reindexRequired();
            }
        }
    }
}
