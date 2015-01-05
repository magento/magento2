<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Class EnableShippingFlatRateFixture
 */
class EnableShippingFlatRateFixture extends \Magento\ToolkitFramework\Fixture
{
    /**
     * @var int
     */
    protected $priority = 110;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->application->resetObjectManager();
        /**
         * @var \Magento\Framework\App\Config\Value $configData
         */
        $configData = $this->application->getObjectManager()->create('Magento\Framework\App\Config\Value');
        $configData->setPath('carriers/flatrate/active')
            ->setScope(\Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT)
            ->setScopeId(0)
            ->setValue(1)
            ->save();

        $this->application->getObjectManager()->get('Magento\Framework\App\CacheInterface')
            ->clean([\Magento\Framework\App\Config::CACHE_TAG]);
    }

    /**
     * {@inheritdoc}
     */
    public function getActionTitle()
    {
        return 'Enabling Flat Rate shipping method';
    }

    /**
     * {@inheritdoc}
     */
    public function introduceParamLabels()
    {
        return [];
    }
}

return new EnableShippingFlatRateFixture($this);
