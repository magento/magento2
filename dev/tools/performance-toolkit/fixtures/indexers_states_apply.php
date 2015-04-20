<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Class IndexersStatesApplyFixture
 */
class IndexersStatesApplyFixture extends \Magento\ToolkitFramework\Fixture
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
        $indexers = \Magento\ToolkitFramework\Config::getInstance()->getValue('indexers', []);
        if (!isset($indexers["indexer"]) || empty($indexers["indexer"])) {
            return;
        }
        $this->application->resetObjectManager();
        foreach ($indexers["indexer"] as $indexer) {
            $this->application->indexersStates[$indexer['id']] = ($indexer['set_scheduled'] == "true");
        }
        $this->application->getObjectManager()->get('Magento\Framework\App\CacheInterface')
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

return new IndexersStatesApplyFixture($this);
