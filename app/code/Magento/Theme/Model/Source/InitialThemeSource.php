<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Source;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\DataObject;
use Magento\Theme\Model\ResourceModel\Theme;
use Magento\Theme\Model\ResourceModel\ThemeFactory;

/**
 * Class InitialThemeSource.
 *
 * Retrieves theme configurations by path.
 */
class InitialThemeSource implements ConfigSourceInterface
{
    /**
     * A deployment config.
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * A theme factory.
     *
     * @var ThemeFactory
     */
    private $themeFactory;

    /**
     * Array with theme data.
     *
     * @var array
     */
    private $data;

    /**
     * @param DeploymentConfig $deploymentConfig A deployment config
     * @param ThemeFactory $themeFactory A theme factory
     */
    public function __construct(DeploymentConfig $deploymentConfig, ThemeFactory $themeFactory)
    {
        $this->deploymentConfig = $deploymentConfig;
        $this->themeFactory = $themeFactory;
    }

    /**
     * Retrieves configuration data array.
     *
     * @param string $path The path to theme configuration.
     * @return array The data array with theme configurations.
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
                unset($themeRow['theme_id']);

                $themes[$themeRow['code']] = $themeRow;

                if (isset($rawThemes[$themeRow['parent_id']]['code'])) {
                    $themes[$themeRow['code']]['parent_id'] = $rawThemes[$themeRow['parent_id']]['code'];
                }
            }

            $this->data = new DataObject($themes);
        }

        return $this->data->getData($path) ?: [];
    }

    /**
     * Fetches themes from data source.
     *
     * @return array An associative list with found themes
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
