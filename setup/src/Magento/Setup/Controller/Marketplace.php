<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Zend\View\Model\JsonModel;
use Magento\Setup\Model\PackagesData;
use Magento\Setup\Model\PackagesAuth;

class Marketplace extends AbstractActionController
{
    /**
     * @var PackagesAuth
     */
    private $packagesAuth;

    /**
     * @var PackagesData
     */
    private $packagesData;

    /**
     * @param PackagesAuth $packagesAuth
     * @param PackagesData $packagesData
     */
    public function __construct(PackagesAuth $packagesAuth, PackagesData $packagesData)
    {
        $this->packagesAuth = $packagesAuth;
        $this->packagesData = $packagesData;
    }

    /**
     * No index action, return 404 error page
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $view = new ViewModel;
        $view->setTemplate('/error/404.phtml');
        $this->getResponse()->setStatusCode(\Zend\Http\Response::STATUS_CODE_404);
        return $view;
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
            $isValid = $this->packagesAuth->checkCredentials($userName, $password);
            $isValid = json_decode($isValid, true);
            if ($isValid['success'] === true && $this->packagesAuth->saveAuthJson($userName, $password)) {
                $this->packagesData->syncPackagesData();
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
            $authDataJson = $this->packagesAuth->getAuthJsonData();
            if ($authDataJson) {
                $isValid = $this->packagesAuth->checkCredentials($authDataJson['username'], $authDataJson['password']);
                $isValid = json_decode($isValid, true);
                if ($isValid['success'] === true) {
                    return new JsonModel(['success' => true, 'data' => [
                        PackagesAuth::KEY_USERNAME => $authDataJson[PackagesAuth::KEY_USERNAME]
                    ]]);
                } else {
                    return new JsonModel(['success' => false, 'message' => $isValid['message']]);
                }
            }
            return new JsonModel(['success' => false, 'data' => [
                PackagesAuth::KEY_USERNAME => $authDataJson[PackagesAuth::KEY_USERNAME]
            ]]);
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
            $result = $this->packagesAuth->removeCredentials();
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
