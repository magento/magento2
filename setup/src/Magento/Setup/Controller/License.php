<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Setup\Model\License as LicenseModel;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

/**
 * License controller
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
        $view = new ViewModel();
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
