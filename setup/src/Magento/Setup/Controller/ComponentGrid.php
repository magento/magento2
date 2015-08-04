<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Framework\Composer\ComposerInformation;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * Controller for component grid tasks
 */
class ComponentGrid extends AbstractActionController
{
    /**
     * @var ComposerInformation
     */
    private $composerInformation;

    /** @var array */
    private static $componentTypes = ['magento2-theme', 'magento2-language', 'magento2-module'];

    /**
     * @param ComposerInformation $composerInformation
     */
    public function __construct(ComposerInformation $composerInformation)
    {
        $this->composerInformation = $composerInformation;
    }

    /**
     * Index page action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTerminal(true);
        return $view;
    }

    /**
     * Get Components info action
     *
     * @return JsonModel
     */
    public function componentsAction()
    {
        $components = $this->filterComponentsList($this->composerInformation->getInstalledMagentoPackages());
        $lastSyncData = $this->composerInformation->getPackagesForUpdate();
        if (is_array($lastSyncData)) {
            $lastSyncData = $this->filterComponentsList($lastSyncData);
        }
        return new JsonModel(
            [
                'success' => true,
                'components' => $components,
                'total' => count($components),
                'lastSyncData' => $lastSyncData
            ]
        );
    }

    /**
     * Filter components list for the grid
     *
     * @param array $components
     * @return array
     */
    private function filterComponentsList($components)
    {
        foreach ($components as $component) {
            if (!in_array($component['type'], self::$componentTypes)) {
                unset($components[$component['name']]);
            }
        }
        return $components;
    }

    /**
     * Sync action
     *
     * @return JsonModel
     */
    public function syncAction()
    {
        $this->composerInformation->syncPackagesForUpdate();
        $lastSyncData = $this->composerInformation->getPackagesForUpdate();
        return new JsonModel(
            [
                'success' => true,
                'lastSyncData' => $lastSyncData
            ]
        );
    }
}
