<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Config;

use Magento\Framework\App\DeploymentConfig\ImporterInterface;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Theme\Model\ResourceModel\Theme as ThemeResourceModel;
use Magento\Theme\Model\ResourceModel\Theme\Data\Collection as ThemeDbCollection;
use Magento\Theme\Model\ResourceModel\Theme\Data\CollectionFactory;
use Magento\Theme\Model\Theme\Collection as ThemeFilesystemCollection;
use Magento\Theme\Model\Theme\Data;
use Magento\Theme\Model\Theme\Registration;

/**
 * Imports themes from configurations files.
 *
 * If a theme is not presented in the configuration files and in the filesystem, but is presented in the DB -
 * removes its registration.
 * If a theme is not presented in the configuration files and in the DB, but is presented in the filesystem -
 * register the theme in the DB.
 * If a theme is presented in the configuration files and in the filesystem, but is not presented in the DB -
 * register the theme in the DB.
 * In other cases - do nothing.
 * @since 2.2.0
 */
class Importer implements ImporterInterface
{
    /**
     * Collection of themes from the filesystem.
     *
     * @var ThemeFilesystemCollection
     * @since 2.2.0
     */
    private $themeFilesystemCollection;

    /**
     * Factory of themes collection from the DB.
     *
     * @var CollectionFactory
     * @since 2.2.0
     */
    private $themeCollectionFactory;

    /**
     * Registrar of themes registers themes in the DB.
     *
     * @var Registration
     * @since 2.2.0
     */
    private $themeRegistration;

    /**
     * Resource model of theme.
     *
     * @var ThemeResourceModel
     * @since 2.2.0
     */
    private $themeResourceModel;

    /**
     * @param ThemeFilesystemCollection $themeFilesystemCollection The collection of themes from the filesystem
     * @param CollectionFactory $collectionFactory The factory of themes collection from the DB
     * @param Registration $registration The registrar of themes registers themes in the DB
     * @param ThemeResourceModel $themeResourceModel The resource model of theme
     * @since 2.2.0
     */
    public function __construct(
        ThemeFilesystemCollection $themeFilesystemCollection,
        CollectionFactory $collectionFactory,
        Registration $registration,
        ThemeResourceModel $themeResourceModel
    ) {
        $this->themeFilesystemCollection = $themeFilesystemCollection;
        $this->themeCollectionFactory = $collectionFactory;
        $this->themeRegistration = $registration;
        $this->themeResourceModel = $themeResourceModel;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function import(array $data)
    {
        $messages = ['<info>Theme import was started.</info>'];

        try {
            // Registers themes from filesystem
            $this->themeRegistration->register();

            /** @var ThemeDbCollection $collection */
            $collection = $this->themeCollectionFactory->create();

            // List of themes full paths which are located in filesystem
            $themesInFs = $this->themeFilesystemCollection->getAllIds();

            /**
             * Removes themes if they are not present in configuration files and filesystem
             * @var Data $theme
             */
            foreach ($collection->getItems() as $theme) {
                $themeFullPath = $theme->getFullPath();
                if (!key_exists($themeFullPath, $data) && !in_array($themeFullPath, $themesInFs)) {
                    $this->themeResourceModel->delete($theme);
                }
            }
        } catch (\Exception $exception) {
            throw new InvalidTransitionException(__('%1', $exception->getMessage()), $exception);
        }

        $messages[] = '<info>Theme import finished.</info>';

        return $messages;
    }

    /**
     * Returns array of warning messages which contain information about which changes (removing, registration)
     * will be applied to themes.
     *
     * @param array $data The data that should be imported, used for creating warning messages
     * @return array
     * @since 2.2.0
     */
    public function getWarningMessages(array $data)
    {
        $themesInFile = array_keys($data);
        $themesInDb = [];

        /** @var ThemeDbCollection $collection */
        $collection = $this->themeCollectionFactory->create();

        /** @var Data $theme */
        foreach ($collection->getItems() as $theme) {
            $themesInDb[] = $theme->getFullPath();
        }

        $toBeRegistered = $this->themeFilesystemCollection->getAllIds();
        $toBeRemoved = array_diff($themesInDb, $toBeRegistered, $themesInFile);
        $newThemes = array_diff($toBeRegistered, $themesInDb);

        $messages = [];

        if ($newThemes) {
            $messages[] = '<info>The following themes will be registered:</info> ' . implode(', ', $newThemes);
        }

        if ($toBeRemoved) {
            $messages[] = '<info>The following themes will be removed:</info> ' . implode(', ', $toBeRemoved);
        }

        return $messages;
    }
}
