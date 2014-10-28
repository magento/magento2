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
namespace Magento\Indexer\Model\Indexer;

class Collection extends \Magento\Framework\Data\Collection
{
    /**
     * Item object class name
     *
     * @var string
     */
    protected $_itemObjectClass = 'Magento\Indexer\Model\IndexerInterface';

    /**
     * @var \Magento\Indexer\Model\ConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Indexer\Model\Resource\Indexer\State\CollectionFactory
     */
    protected $statesFactory;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Magento\Indexer\Model\ConfigInterface $config
     * @param \Magento\Indexer\Model\Resource\Indexer\State\CollectionFactory $statesFactory
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Magento\Indexer\Model\ConfigInterface $config,
        \Magento\Indexer\Model\Resource\Indexer\State\CollectionFactory $statesFactory
    ) {
        $this->config = $config;
        $this->statesFactory = $statesFactory;
        parent::__construct($entityFactory);
    }

    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return \Magento\Indexer\Model\Indexer\Collection
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if (!$this->isLoaded()) {
            $states = $this->statesFactory->create();
            foreach (array_keys($this->config->getIndexers()) as $indexerId) {
                /** @var \Magento\Indexer\Model\IndexerInterface $indexer */
                $indexer = $this->getNewEmptyItem();
                $indexer->load($indexerId);
                foreach ($states->getItems() as $state) {
                    /** @var \Magento\Indexer\Model\Indexer\State $state */
                    if ($state->getIndexerId() == $indexerId) {
                        $indexer->setState($state);
                        break;
                    }
                }
                $this->_addItem($indexer);
            }
            $this->_setIsLoaded(true);
        }
        return $this;
    }
}
