<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

/**
 * Class IndexersStatesApplyFixture
 * @since 2.0.0
 */
class IndexersStatesApplyFixture extends Fixture
{
    /**
     * @var int
     * @since 2.0.0
     */
    protected $priority = 170;

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getActionTitle()
    {
        return 'Indexers Mode Changes';
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function introduceParamLabels()
    {
        return [];
    }
}
