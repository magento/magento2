<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ThemeSampleData\Setup;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup;
use Magento\Store\Model\Store;

class Installer implements Setup\SampleData\InstallerInterface
{
    /**
     * @var \Magento\Theme\Model\Config
     */
    private $config;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory
     */
    private $collectionFactory;

    /**
     * Setup class for css
     *
     * @var \Magento\ThemeSampleData\Model\Css
     */
    private $css;

    /**
     * @param \Magento\Theme\Model\Config $config
     * @param \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $collectionFactory
     * @param \Magento\ThemeSampleData\Model\Css $css
     */
    public function __construct(
        \Magento\Theme\Model\Config $config,
        \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $collectionFactory,
        \Magento\ThemeSampleData\Model\Css $css
    ) {
        $this->config = $config;
        $this->collectionFactory = $collectionFactory;
        $this->css = $css;
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        $this->assignTheme();
        $this->css->install(['Magento_CmsSampleData::fixtures/styles.css' => 'styles.css']);
    }

    /**
     * Assign Theme
     *
     * @return void
     */
    protected function assignTheme()
    {
        $themes = $this->collectionFactory->create()->loadRegisteredThemes();
        /** @var \Magento\Theme\Model\Theme $theme */
        foreach ($themes as $theme) {
            if ($theme->getCode() == 'Magento/luma') {
                $this->config->assignToStore(
                    $theme,
                    [Store::DEFAULT_STORE_ID],
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                );
            }
        }
    }
}