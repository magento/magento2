<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Magento\Setup\Model\License as LicenseModel;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Class LicenseController
 *
 * @package Magento\Setup\Controller
 */
class License extends AbstractActionController
{
    /**
     * Licence Model
     *
     * @var LicenseModel
     */
    protected $license;

    /**
     * Constructor
     *
     * @param LicenseModel $license
     */
    public function __construct(LicenseModel $license)
    {
        $this->license = $license;
    }

    /**
     * Displays license
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $contents = $this->license->getContents();
        $view = new ViewModel;
        if ($contents === false) {
            $view->setTemplate('error/404');
            $view->setVariable('message', 'Cannot find license file.');
        } else {
            $view->setTerminal(true);
            $view->setVariable('license', $contents);
        }
        return $view;
    }
}
