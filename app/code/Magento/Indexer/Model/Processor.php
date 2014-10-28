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
namespace Magento\Indexer\Model;

class Processor
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var IndexerFactory
     */
    protected $indexerFactory;

    /**
     * @var Indexer\CollectionFactory
     */
    protected $indexersFactory;

    /**
     * @var \Magento\Framework\Mview\ProcessorInterface
     */
    protected $mviewProcessor;

    /**
     * @param ConfigInterface $config
     * @param IndexerFactory $indexerFactory
     * @param Indexer\CollectionFactory $indexersFactory
     * @param \Magento\Framework\Mview\ProcessorInterface $mviewProcessor
     */
    public function __construct(
        ConfigInterface $config,
        IndexerFactory $indexerFactory,
        Indexer\CollectionFactory $indexersFactory,
        \Magento\Framework\Mview\ProcessorInterface $mviewProcessor
    ) {
        $this->config = $config;
        $this->indexerFactory = $indexerFactory;
        $this->indexersFactory = $indexersFactory;
        $this->mviewProcessor = $mviewProcessor;
    }

    /**
     * Regenerate indexes for all invalid indexers
     *
     * @return void
     */
    public function reindexAllInvalid()
    {
        foreach (array_keys($this->config->getIndexers()) as $indexerId) {
            $indexer = $this->indexerFactory->create();
            $indexer->load($indexerId);
            if ($indexer->isInvalid()) {
                $indexer->reindexAll();
            }
        }
    }

    /**
     * Regenerate indexes for all indexers
     *
     * @return void
     */
    public function reindexAll()
    {
        /** @var IndexerInterface[] $indexers */
        $indexers = $this->indexersFactory->create()->getItems();
        foreach ($indexers as $indexer) {
            $indexer->reindexAll();
        }
    }

    /**
     * Update indexer views
     *
     * @return void
     */
    public function updateMview()
    {
        $this->mviewProcessor->update('indexer');
    }

    /**
     * Clean indexer view changelogs
     *
     * @return void
     */
    public function clearChangelog()
    {
        $this->mviewProcessor->clearChangelog('indexer');
    }
}
