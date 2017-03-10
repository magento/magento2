<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Config;

use Magento\Framework\App\DeploymentConfig\ImporterInterface;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\Theme\Collection as ThemeFilesystemCollection;
use Magento\Theme\Model\ResourceModel\Theme\Data\CollectionFactory;
use Magento\Theme\Model\ResourceModel\Theme\Data\Collection as ThemeDbCollection;
use Magento\Theme\Model\Theme\Registration;
use Magento\Theme\Model\Theme\Data;
use Magento\Theme\Model\ResourceModel\Theme as ThemeResourceModel;

/**
 * Imports themes from configurations files.
 *
 * If a theme is presented in the configuration files, in the DB and on the filesystem - does nothing.
 * If a theme is presented in the configuration files, in the DB and is not presented on the filesystem -
 * marks it as virtual.
 * If a theme is presented in the configuration files, but is not presented in the DB and on the filesystem -
 * registers new virtual theme.
 * If a theme is not presented in the configuration files, but is presented in the DB and on the filesystem -
 * removes its registration.
 * If a theme is not presented in the configuration files and on the filesystem, but is presented in the DB -
 * removes its registration.
 * If a theme is not presented in the configuration files and in the DB, but is presented on the filesystem -
 * does nothing.
 */
class Importer implements ImporterInterface
{
    /**
     * Collection of themes from the filesystem.
     *
     * @var ThemeFilesystemCollection
     */
    private $themeFilesystemCollection;

    /**
     * Factory of themes collection from the DB.
     *
     * @var CollectionFactory
     */
    private $themeCollectionFactory;

    /**
     * Registrar of themes registers themes in the DB.
     *
     * @var Registration
     */
    private $themeRegistration;

    /**
     * Resource model of theme.
     *
     * @var ThemeResourceModel
     */
    private $themeResourceModel;

    /**
     * @param ThemeFilesystemCollection $themeFilesystemCollection The collection of themes from the filesystem
     * @param CollectionFactory $collectionFactory The factory of themes collection from the DB
     * @param Registration $registration The registrar of themes registers themes in the DB
     * @param ThemeResourceModel $themeResourceModel The resource model of theme
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
     */
    public function import(array $data)
    {
        $messages = ['<info>Theme import was started.</info>'];

        try {
            // Registers themes from filesystem
            $this->themeRegistration->register();

            /** @var ThemeDbCollection $collection */
            $collection = $this->themeCollectionFactory->create();

            /**
             * Removes themes if they are not present in configuration files
             * @var Data $theme
             */
            foreach ($collection->getItems() as $theme) {
                if (!key_exists($theme->getFullPath(), $data)) {
                    $this->themeResourceModel->delete($theme);
                }
            }

            // Creates virtual theme if its code is absent on filesystem
            foreach ($data as $themePath => $themeData) {
                /** @var Data $theme */
                $theme = $this->themeRegistration->getThemeFromDb($themePath);
                if (!$theme->getId() && $themePath === $themeData['area'] . '/' . $themeData['theme_path']) {
                    $theme->setData($themeData);
                    $theme->setType(ThemeInterface::TYPE_VIRTUAL);
                    $this->themeResourceModel->save($theme);
                }
            }
        } catch (\Exception $exception) {
            throw new InvalidTransitionException(__('%1', $exception->getMessage()), $exception);
        }

        $messages[] = '<info>Theme import was finished.</info>';

        return $messages;
    }

    /**
     * Returns array of warning messages if needed.
     *
     * @param array $data The data that should be imported, used for creating warning messages
     * @return array
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
        $toBeRemoved = array_diff($themesInDb, $themesInFile);
        $toBeVirtual = array_diff($themesInFile, $toBeRegistered);
        $newThemes = array_unique(
            array_merge(
                array_diff($themesInFile, $themesInDb),
                array_diff($themesInFile, $toBeRegistered, $themesInDb)
            )
        );

        $messages = [];
        if ($newThemes || $toBeVirtual || $toBeRemoved) {
            $messages = ['<info>As result of themes importing you will get:</info>'];

            if ($newThemes) {
                $messages[] = '<info>The following themes will be registered:</info>';
                $messages[] = implode(PHP_EOL, $newThemes);
            }

            if ($toBeVirtual) {
                $messages[] = '<info>The following themes will be virtual:</info>';
                $messages[] = implode(PHP_EOL, $toBeVirtual);
            }

            if ($toBeRemoved) {
                $messages[] = '<info>The following themes will be removed:</info>';
                $messages[] = implode(PHP_EOL, $toBeRemoved);
            }
        }

        return $messages;
    }
}
