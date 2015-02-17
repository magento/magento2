<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Class ConfigsApplyFixture
 */
class ConfigsApplyFixture extends \Magento\ToolkitFramework\Fixture
{
    /**
     * @var int
     */
    protected $priority = 150;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $configs = \Magento\ToolkitFramework\Config::getInstance()->getValue('configs', array());
        $this->application->resetObjectManager();

        foreach ($configs['config'] as $config) {
            /**
             * @var \Magento\Framework\App\Config\Value $configData
             */
            $configData = $this->application->getObjectManager()->create('Magento\Framework\App\Config\Value');
            $configData->setPath($config['path'])
                ->setScope($config['scope'])
                ->setScopeId($config['scopeId'])
                ->setValue($config['value'])
                ->save();
        }
        $this->application->getObjectManager()->get('Magento\Framework\App\CacheInterface')
            ->clean([\Magento\Framework\App\Config::CACHE_TAG]);
    }

    /**
     * {@inheritdoc}
     */
    public function getActionTitle()
    {
        return 'Config Changes';
    }

    /**
     * {@inheritdoc}
     */
    public function introduceParamLabels()
    {
        return [];
    }
}

return new ConfigsApplyFixture($this);
