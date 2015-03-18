<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Controller\Adminhtml\AbstractAction;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class Render
 */
class Render extends AbstractAction
{
    /**
     * @var UiComponentFactory
     */
    protected $uiComponentFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param UiComponentFactory $factory
     * @param UiComponentFactory $uiComponentFactory
     */
    public function __construct(
        Context $context,
        UiComponentFactory $factory,
        UiComponentFactory $uiComponentFactory
    ) {
        $this->uiComponentFactory = $uiComponentFactory;
        parent::__construct($context, $factory);
    }

    /**
     * Action for AJAX request
     *
     * @return void
     */
    public function execute()
    {
        $component = $this->uiComponentFactory->create($this->_request->getParam('component_name'));
        $this->prepareComponent($component);
        $this->_response->appendBody((string) $component->render());
    }

    /**
     * Call prepare method in the component UI
     *
     * @param UiComponentInterface $component
     * @return void
     */
    protected function prepareComponent(UiComponentInterface $component)
    {
        $childComponents = $component->getChildComponents();
        if (!empty($childComponents)) {
            foreach ($childComponents as $child) {
                $this->prepareComponent($child);
            }
        }
        $component->prepare();
    }
}
