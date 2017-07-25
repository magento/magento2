<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

/**
 * Class ConfigsApplyFixture
 */
class ConfigsApplyFixture extends Fixture
{
    /**
     * @var int
     */
    protected $priority = 0;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $configs = $this->fixtureModel->getValue('configs', []);
        if (empty($configs)) {
            return;
        }
        $this->fixtureModel->resetObjectManager();

        foreach ($configs['config'] as $config) {
            $backendModel = isset($config['backend_model'])
                ?
                $config['backend_model'] : \Magento\Framework\App\Config\Value::class;
            /**
             * @var \Magento\Framework\App\Config\ValueInterface $configData
             */
            $configData = $this->fixtureModel->getObjectManager()->create($backendModel);
            $configData->setPath($config['path'])
                ->setScope($config['scope'])
                ->setScopeId($config['scopeId'])
                ->setValue($config['value'])
                ->save();
        }
        $this->fixtureModel->getObjectManager()->get(\Magento\Framework\App\CacheInterface::class)
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
