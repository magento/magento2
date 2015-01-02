<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Magento\Framework\App\SetupInfo;

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
        $view = new ViewModel(['autoBaseUrl' => $setupInfo->getProjectUrl()]);
        $view->setTerminal(true);
        return $view;
    }
}
