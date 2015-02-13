<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Class DisableFormKeyUsageFixture
 */
class ConfigsApplyFixture extends \Magento\ToolkitFramework\Fixture
{
    /**
     * @var int
     */
    protected $priority = 100;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $configs = \Magento\ToolkitFramework\Config::getInstance()->getValue('configs', array());

        $this->application->resetObjectManager();
        /**
         * @var \Magento\Framework\App\Config\Value $configData
         */
        $configData = $this->application->getObjectManager()->create('Magento\Framework\App\Config\Value');

        foreach ($configs as $config) {
            try {
                $configData->setPath($config['path'])
                    ->setScope($config['scope'])
                    ->setScopeId($config['scopeId'])
                    ->setValue($config['value'])
                    ->save();
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), '1062 Duplicate entry')) {
                    echo 'Config value the same' . PHP_EOL;
                } else {
                    throw new Exception('Error update config');
                }
            }
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
