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

namespace Magento\Indexer\Model\Processor;

/**
 * Class InvalidateCache
 */
class InvalidateCache
{
    /**
     * @var \Magento\Indexer\Model\CacheContext
     */
    protected $context;

    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @param \Magento\Indexer\Model\CacheContext $context
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        \Magento\Indexer\Model\CacheContext $context,
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->context = $context;
        $this->eventManager = $eventManager;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Update indexer views
     *
     * @param \Magento\Indexer\Model\Processor $subject
     * @return void
     */
    public function afterUpdateMview(\Magento\Indexer\Model\Processor $subject)
    {
        if ($this->moduleManager->isEnabled('Magento_PageCache')) {
            $this->eventManager->dispatch('clean_cache_after_reindex', array('object' => $this->context));
        }
    }
}
