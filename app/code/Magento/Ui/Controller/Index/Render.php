<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Controller\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Is responsible for providing ui components information on store front
 */
class Render extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var UiComponentFactory
     */
    private $uiComponentFactory;

    /**
     * Render constructor.
     * @param Context $context
     * @param UiComponentFactory $uiComponentFactory
     */
    public function __construct(Context $context, UiComponentFactory $uiComponentFactory)
    {
        parent::__construct($context);
        $this->context = $context;
        $this->uiComponentFactory = $uiComponentFactory;
    }

    /**
     * Action for AJAX request
     *
     * @return void
     */
    public function execute()
    {
        if ($this->_request->getParam('namespace') === null) {
            $this->_redirect('noroute');
            return;
        }

        $component = $this->uiComponentFactory->create($this->_request->getParam('namespace'));
        $this->prepareComponent($component);
        $this->_response->appendBody((string) $component->render());
    }

    /**
     * Call prepare method in the component UI
     *
     * @param UiComponentInterface $component
     * @return void
     */
    private function prepareComponent(UiComponentInterface $component)
    {
        foreach ($component->getChildComponents() as $child) {
            $this->prepareComponent($child);
        }
        $component->prepare();
    }
}
