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
 * @package     Magento_Index
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Index\Model;

/**
 * Indexer interface
 */
interface IndexerInterface
{
    /**
     * Get indexer name
     *
     * @return mixed
     */
    public function getName();

    /**
     * Get Indexer description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Register data required by process in event object
     *
     * @param \Magento\Index\Model\Event $event
     * @return \Magento\Index\Model\IndexerInterface
     */
    public function register(\Magento\Index\Model\Event $event);

    /**
     * Process event
     *
     * @param \Magento\Index\Model\Event $event
     * @return \Magento\Index\Model\IndexerInterface
     */
    public function processEvent(\Magento\Index\Model\Event $event);

    /**
     * Check if event can be matched by process
     *
     * @param \Magento\Index\Model\Event $event
     * @return bool
     */
    public function matchEvent(\Magento\Index\Model\Event $event);

    /**
     * Check if indexer matched specific entity and action type
     *
     * @param   string $entity
     * @param   string $type
     * @return  bool
     */
    public function matchEntityAndType($entity, $type);

    /**
     * Rebuild all index data
     */
    public function reindexAll();

    /**
     * Try dynamicly detect and call event hanler from resource model.
     * Handler name will be generated from event entity and type code
     *
     * @param   \Magento\Index\Model\Event $event
     * @return  \Magento\Index\Model\Indexer\AbstractIndexer
     */
    public function callEventHandler(\Magento\Index\Model\Event $event);

    /**
     * Whether the indexer should be displayed on process/list page
     *
     * @return bool
     */
    public function isVisible();
}
