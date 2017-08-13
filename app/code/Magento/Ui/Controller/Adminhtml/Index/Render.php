<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Controller\Adminhtml\AbstractAction;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContentType;

/**
 * Class Render
 */
class Render extends AbstractAction
{
    /**
     * Action for AJAX request
     *
     * @return void
     */
    public function execute()
    {
        if ($this->_request->getParam('namespace') === null) {
            $this->_redirect('admin/noroute');
            return;
        }

        $component = $this->factory->create($this->_request->getParam('namespace'));
        $this->prepareComponent($component);
        $this->_response->appendBody((string) $component->render());
        $this->setResponseContentTypeHeader($component);
    }

    /**
     * Call prepare method in the component UI
     *
     * @param UiComponentInterface $component
     * @return void
     */
    protected function prepareComponent(UiComponentInterface $component)
    {
        foreach ($component->getChildComponents() as $child) {
            $this->prepareComponent($child);
        }
        $component->prepare();
    }

    /**
     * Set the response 'Content-Type' header based on the component's render engine
     *
     * @param UiComponentInterface $component
     * @return void
     */
    private function setResponseContentTypeHeader(UiComponentInterface $component)
    {
        $contentType = 'text/html';

        if ($component->getContext()) {
            $renderEngine = $component->getContext()->getRenderEngine();
            if ($renderEngine instanceof ContentType\Json) {
                $contentType = 'application/json';
            } elseif ($renderEngine instanceof ContentType\Xml) {
                $contentType = 'application/xml';
            }
        }

        $this->_response->setHeader('Content-Type', $contentType, true);
    }
}
