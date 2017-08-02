<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
 * @since 2.0.0
 */
class License extends AbstractActionController
{
    /**
     * Licence Model
     *
     * @var LicenseModel
     * @since 2.0.0
     */
    protected $license;

    /**
     * Constructor
     *
     * @param LicenseModel $license
     * @since 2.0.0
     */
    public function __construct(LicenseModel $license)
    {
        $this->license = $license;
    }

    /**
     * Displays license
     *
     * @return ViewModel
     * @since 2.0.0
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
