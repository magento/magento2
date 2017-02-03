<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * Theme resource factory
     *
     * @var \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory
     */
    private $themeResourceFactory;

    /**
     * Theme collection factory
     *
     * @var \Magento\Theme\Model\Theme\CollectionFactory
     */
    private $themeFactory;

    /**
     * Init
     *
     * @param \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $themeResourceFactory
     * @param \Magento\Theme\Model\Theme\CollectionFactory $themeFactory
     */
    public function __construct(
        \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $themeResourceFactory,
        \Magento\Theme\Model\Theme\CollectionFactory $themeFactory
    ) {
        $this->themeResourceFactory = $themeResourceFactory;
        $this->themeFactory = $themeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /*
         * Register themes
         */
        $setup->getEventManager()->dispatch('theme_registration_from_filesystem');

        /**
         * Update theme's data
         */
        $fileCollection = $this->createTheme();
        $fileCollection->setItemObjectClass('Magento\Theme\Model\Theme\Data');

        $resourceCollection = $this->createThemeResource();
        $resourceCollection->setItemObjectClass('Magento\Theme\Model\Theme\Data');

        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        foreach ($resourceCollection as $theme) {
            $themeType = $fileCollection->hasTheme($theme)
                ? \Magento\Framework\View\Design\ThemeInterface::TYPE_PHYSICAL
                : \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL;
            $theme->setType($themeType)->save();
        }

        $fileCollection = $this->createTheme();
        $fileCollection->setItemObjectClass('Magento\Theme\Model\Theme\Data');

        $themeDbCollection = $this->createThemeResource();
        $themeDbCollection->setItemObjectClass('Magento\Theme\Model\Theme\Data');

        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        foreach ($fileCollection as $theme) {
            $dbTheme = $themeDbCollection->getThemeByFullPath($theme->getFullPath());
            $dbTheme->setCode($theme->getCode());
            $dbTheme->save();
        }

        /**
         * Update rows in theme
         */
        $setup->getConnection()->update(
            $setup->getTable('theme'),
            ['area' => 'frontend'],
            ['area = ?' => '']
        );
    }

    /**
     * @return \Magento\Theme\Model\ResourceModel\Theme\Collection
     */
    public function createThemeResource()
    {
        return $this->themeResourceFactory->create();
    }

    /**
     * @return \Magento\Theme\Model\Theme\Collection
     */
    public function createTheme()
    {
        return $this->themeFactory->create();
    }
}
