<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Zend\View\Model\JsonModel;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Setup\Model\MarketplaceManager;

class Marketplace extends AbstractActionController
{

    /**
     * @var ComposerInformation
     */
    private $composerInformation;

    /**
     * @var MarketplaceManager
     */
    private $marketplaceManager;

    /**
     * @param ComposerInformation $composerInformation
     * @param MarketplaceManager $marketplaceManager
     */
    public function __construct(ComposerInformation $composerInformation, MarketplaceManager $marketplaceManager)
    {
        $this->composerInformation = $composerInformation;
        $this->marketplaceManager = $marketplaceManager;
    }

    /**
     * Save auth.json
     *
     * @return array|ViewModel
     */
    public function saveAuthJsonAction()
    {
        $params = [];
        if ($this->getRequest()->getContent()) {
            $params = Json::decode($this->getRequest()->getContent(), Json::TYPE_ARRAY);
        }
        try {
            $userName = isset($params['username']) ? $params['username'] : '';
            $password = isset($params['password']) ? $params['password'] : '';
            $isValid = $this->marketplaceManager->checkCredentialsAction($userName, $password);
            $isValid = json_decode($isValid, true);
            if ($isValid['success'] === true && $this->marketplaceManager->saveAuthJson($userName, $password)) {
                return new JsonModel(['success' => true]);
            } else {
                return new JsonModel(['success' => false, 'message' => $isValid['message']]);
            }
        } catch (\Exception $e) {
            return new JsonModel(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Check if user authorize in connect
     *
     * @return JsonModel
     */
    public function checkAuthAction()
    {
        try {
            $authDataJson = $this->marketplaceManager->getAuthJsonData();
            if ($authDataJson) {
                $isValid = $this->marketplaceManager->checkCredentialsAction(
                    $authDataJson['username'],
                    $authDataJson['password']
                );
                $isValid = json_decode($isValid, true);
                if ($isValid['success'] === true) {
                    return new JsonModel(['success' => true, 'data' => $authDataJson]);
                } else {
                    return new JsonModel(['success' => false, 'message' => $isValid['message']]);
                }
            }
            return new JsonModel(['success' => false, 'data' => $authDataJson]);
        } catch (\Exception $e) {
            return new JsonModel(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Remove credentials from auth.json
     *
     * @return JsonModel
     */
    public function removeCredentialsAction()
    {
        try {
            $result = $this->marketplaceManager->removeCredentials();
            return new JsonModel(['success' => $result]);
        } catch (\Exception $e) {
            return new JsonModel(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * @return array|ViewModel
     */
    public function popupAuthAction()
    {
        $view = new ViewModel();
        $view->setTemplate('/magento/setup/popupauth.phtml');
        $view->setTerminal(true);
        return $view;
    }
}
