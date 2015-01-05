<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

class EnableShippingFlatRateFixture extends \Magento\ToolkitFramework\Fixture
{
    protected $priority = 110;

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

    public function getActionTitle()
    {
        return 'Enabling Flat Rate shipping method';
    }

    public function introduceParamLabels()
    {
        return [];
    }
}

return new EnableShippingFlatRateFixture($this);
