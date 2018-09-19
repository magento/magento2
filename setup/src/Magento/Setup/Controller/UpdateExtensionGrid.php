<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Magento\Setup\Model\Grid;

/**
 * Controller for update extensions grid tasks
 */
class UpdateExtensionGrid extends AbstractActionController
{
    /**
     * @var Grid\Extension
     */
    private $gridExtension;

    /**
     * @param Grid\Extension $gridExtension
     */
    public function __construct(Grid\Extension $gridExtension)
    {
        $this->gridExtension = $gridExtension;
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
     * Get extensions action
     *
     * @return JsonModel
     */
    public function extensionsAction()
    {
        $extensions = $this->gridExtension->getListForUpdate();

        return new JsonModel(
            [
                'success' => true,
                'extensions' => array_values($extensions),
                'total' => count($extensions)
            ]
        );
    }
}
