<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Class IndexersApplyFixture
 */
class IndexersApplyFixture extends \Magento\ToolkitFramework\Fixture
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
        $indexers = \Magento\ToolkitFramework\Config::getInstance()->getValue('indexers', array());
        $this->application->resetObjectManager();

                foreach ($indexers["indexer"] as $indexer) {
                    $this->application->indexersStates[$indexer['id']] = $indexer['set_scheduled'];
                }

        $this->application->getObjectManager()->get('Magento\Framework\App\CacheInterface')
            ->clean([\Magento\Framework\App\Config::CACHE_TAG]);
    }

    /**
     * {@inheritdoc}
     */
    public function getActionTitle()
    {
        return 'Indexer Mode Changes';
    }

    /**
     * {@inheritdoc}
     */
    public function introduceParamLabels()
    {
        return [];
    }
}

return new IndexersApplyFixture($this);
