<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Controller\UiActionInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class Render
 * @api
 * @since 2.0.0
 */
abstract class AbstractAction extends Action implements UiActionInterface
{
    /**
     * @var UiComponentFactory
     * @since 2.0.0
     */
    protected $factory;

    /**
     * @param Context $context
     * @param UiComponentFactory $factory
     * @since 2.0.0
     */
    public function __construct(Context $context, UiComponentFactory $factory)
    {
        parent::__construct($context);
        $this->factory = $factory;
    }

    /**
     * Getting name
     *
     * @return mixed
     * @since 2.0.0
     */
    protected function getName()
    {
        return $this->_request->getParam('name');
    }

    /**
     * Getting component
     *
     * @return mixed
     * @since 2.0.0
     */
    protected function getComponent()
    {
        return $this->_request->getParam('component');
    }

    /**
     * Action for AJAX request
     *
     * @return void
     * @since 2.0.0
     */
    public function executeAjaxRequest()
    {
        $this->execute();
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    protected function _isAllowed()
    {
        return true;
    }
}
