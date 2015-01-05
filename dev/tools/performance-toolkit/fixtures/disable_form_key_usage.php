<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Class DisableFormKeyUsageFixture
 */
class DisableFormKeyUsageFixture extends \Magento\ToolkitFramework\Fixture
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
        $this->application->resetObjectManager();
        /**
         * @var \Magento\Framework\App\Config\Value $configData
         */
        $configData = $this->application->getObjectManager()->create('Magento\Framework\App\Config\Value');
        $configData->setPath(\Magento\Backend\Model\Url::XML_PATH_USE_SECURE_KEY)
            ->setScope(\Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT)
            ->setScopeId(0)
            ->setValue(0)
            ->save();

        $this->application->getObjectManager()->get('Magento\Framework\App\CacheInterface')
            ->clean([\Magento\Framework\App\Config::CACHE_TAG]);
    }

    /**
     * {@inheritdoc}
     */
    public function getActionTitle()
    {
        return 'Disabling form key usage';
    }

    /**
     * {@inheritdoc}
     */
    public function introduceParamLabels()
    {
        return [];
    }
}

return new DisableFormKeyUsageFixture($this);
