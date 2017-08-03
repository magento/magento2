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
 * @since 2.2.0
 */
class UpdateExtensionGrid extends AbstractActionController
{
    /**
     * @var Grid\Extension
     * @since 2.2.0
     */
    private $gridExtension;

    /**
     * @param Grid\Extension $gridExtension
     * @since 2.2.0
     */
    public function __construct(Grid\Extension $gridExtension)
    {
        $this->gridExtension = $gridExtension;
    }

    /**
     * Index page action
     *
     * @return ViewModel
     * @since 2.2.0
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
     * @since 2.2.0
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
