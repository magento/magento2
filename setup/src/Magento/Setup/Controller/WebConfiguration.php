<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Framework\App\SetupInfo;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class WebConfiguration extends AbstractActionController
{
    /**
     * Displays web configuration form
     *
     * @return array|ViewModel
     */
    public function indexAction()
    {
        $setupInfo = new SetupInfo($_SERVER);
        $view = new ViewModel(
            [
                'autoBaseUrl'   => $setupInfo->getProjectUrl(),
                'autoAdminPath' => $setupInfo->getProjectAdminPath(),
                'sessionSave'   => [
                        ConfigOptionsListConstants::SESSION_SAVE_FILES,
                        ConfigOptionsListConstants::SESSION_SAVE_DB,
                        ConfigOptionsListConstants::SESSION_SAVE_REDIS,
                    ],
            ]
        );
        $view->setTerminal(true);
        return $view;
    }

    /**
     * Checks if redis extension exists
     *
     * @return array|JsonModel
     */
    public function hasRedisAction()
    {
        $json = new JsonModel(['hasRedis' => extension_loaded('redis')]);
        $json->setTerminal(true);
        return $json;
    }
}
