<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Source;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Theme\Model\ResourceModel\Theme;
use Magento\Theme\Model\ResourceModel\ThemeFactory;

/**
 * Class InitialThemeSource.
 *
 * Retrieves theme configurations by path.
 * @since 2.2.0
 */
class InitialThemeSource implements ConfigSourceInterface
{
    /**
     * A deployment config.
     *
     * @var DeploymentConfig
     * @since 2.2.0
     */
    private $deploymentConfig;

    /**
     * A theme factory.
     *
     * @var ThemeFactory
     * @since 2.2.0
     */
    private $themeFactory;

    /**
     * A data object factory.
     *
     * @var DataObjectFactory
     * @since 2.2.0
     */
    private $dataObjectFactory;

    /**
     * Array with theme data.
     *
     * @var array
     * @since 2.2.0
     */
    private $data;

    /**
     * @param DeploymentConfig $deploymentConfig A deployment config
     * @param ThemeFactory $themeFactory A theme factory
     * @param DataObjectFactory $dataObjectFactory A data object factory
     * @since 2.2.0
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        ThemeFactory $themeFactory,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->themeFactory = $themeFactory;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Retrieves configuration data array.
     * Example:
     *
     *  ```php
     *  ['adminhtml/Magento/backend' =>
     *      [
     *          'parent_id' => NULL,
     *          'theme_path' => 'Magento/backend',
     *          'theme_title' => 'Magento 2 backend',
     *          'is_featured' => '0',
     *          'area' => 'adminhtml',
     *          'type' => '0',
     *          'code' => 'Magento/backend',
     *      ]
     *  ]
     *  ```
     *
     * @param string $path The path to theme configuration.
     * @return array The data array with theme configurations.
     * @since 2.2.0
     */
    public function get($path = '')
    {
        if (!$this->deploymentConfig->isDbAvailable()) {
            return [];
        }

        if (!$this->data) {
            $rawThemes = $this->fetchThemes();
            $themes = [];

            foreach ($rawThemes as $themeRow) {
                unset($themeRow['theme_id'], $themeRow['preview_image']);
                $themePath = $themeRow['area'] . '/' . $themeRow['theme_path'];
                $themes[$themePath] = $themeRow;

                if (isset($rawThemes[$themeRow['parent_id']]['code'])) {
                    $themes[$themePath]['parent_id'] = $rawThemes[$themeRow['parent_id']]['code'];
                }
            }

            $this->data = $this->dataObjectFactory->create($themes);
        }

        return $this->data->getData($path) ?: [];
    }

    /**
     * Fetches themes from data source.
     *
     * @return array An associative list with found themes
     * @since 2.2.0
     */
    private function fetchThemes()
    {
        /** @var Theme $theme */
        $theme = $this->themeFactory->create();
        $select = $theme->getConnection()->select()
            ->from($theme->getMainTable())
            ->order('theme_id');

        return $theme->getConnection()->fetchAssoc($select);
    }
}
