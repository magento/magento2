<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

/**
 * Class IndexersStatesApplyFixture
 */
class IndexersStatesApplyFixture extends Fixture
{
    /**
     * @var int
     */
    protected $priority = 170;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $indexers = $this->fixtureModel->getValue('indexers', []);
        if (!isset($indexers["indexer"]) || empty($indexers["indexer"])) {
            return;
        }
        $this->fixtureModel->resetObjectManager();
        foreach ($indexers["indexer"] as $indexer) {
            $this->fixtureModel->indexersStates[$indexer['id']] = ($indexer['set_scheduled'] == "true");
        }
        $this->fixtureModel->getObjectManager()->get(\Magento\Framework\App\CacheInterface::class)
            ->clean([\Magento\Framework\App\Config::CACHE_TAG]);
    }

    /**
     * {@inheritdoc}
     */
    public function getActionTitle()
    {
        return 'Indexers Mode Changes';
    }

    /**
     * {@inheritdoc}
     */
    public function introduceParamLabels()
    {
        return [];
    }
}
