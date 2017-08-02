<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Composer\InfoCommand;
use Magento\Setup\Model\SystemPackage;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * Controller for selecting version
 * @since 2.0.0
 */
class SelectVersion extends AbstractActionController
{
    /**
     * @var SystemPackage
     * @since 2.0.0
     */
    protected $systemPackage;

    /**
     * @param SystemPackage $systemPackage
     * @since 2.0.0
     */
    public function __construct(
        SystemPackage $systemPackage
    ) {
        $this->systemPackage = $systemPackage;
    }

    /**
     * @return ViewModel|\Zend\Http\Response
     * @since 2.0.0
     */
    public function indexAction()
    {
        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setTemplate('/magento/setup/select-version.phtml');
        return $view;
    }

    /**
     * Gets system package and versions
     *
     * @return JsonModel
     * @since 2.0.0
     */
    public function systemPackageAction()
    {
        $data = [];
        try {
            $data['packages'] = $this->systemPackage->getPackageVersions();
            $responseType = ResponseTypeInterface::RESPONSE_TYPE_SUCCESS;
        } catch (\Exception $e) {
            $responseType = ResponseTypeInterface::RESPONSE_TYPE_ERROR;
            $data['error'] = $e->getMessage();
        }
        $data['responseType'] = $responseType;

        return new JsonModel($data);
    }

    /**
     * Gets installed system package
     *
     * @return JsonModel
     * @since 2.1.0
     */
    public function installedSystemPackageAction()
    {
        $data = [];
        try {
            $data['packages'] = $this->systemPackage->getInstalledSystemPackages();
            $data['responseType'] = ResponseTypeInterface::RESPONSE_TYPE_SUCCESS;
        } catch (\Exception $e) {
            $data['error'] = $e->getMessage();
            $data['responseType'] = ResponseTypeInterface::RESPONSE_TYPE_ERROR;
        }
        return new JsonModel($data);
    }
}
