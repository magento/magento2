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
    const XML_VERSION = '1.0';

    const XML_ENCODING = 'UTF-8';
    /**
     * Action for AJAX request
     *
     * @return void
     */
    public function execute()
    {
        $component = $this->factory->create($this->_request->getParam('namespace'));
        $this->prepareComponent($component);
        if ($this->_request->getParam('type') === 'template') {
            /** @var \Magento\Framework\View\Element\UiComponent\Config\Provider\Template $templateProvider */
            $templateProvider = $this->_objectManager->get('\Magento\Framework\View\Element\UiComponent\Config\Provider\Template');
            $template = $templateProvider->getTemplate($component->getTemplate());
            $document = new \DOMDocument(static::XML_VERSION, static::XML_ENCODING);
            $document->loadXML($template);
            $document->documentElement->removeAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi');
            $template = $document->saveHTML();
            /** @var \Magento\Framework\View\Layout\Generator\Structure $structure */
            $structure = $this->_objectManager->get('Magento\Framework\View\Layout\Generator\Structure');
            $result = $template.$this->wrapContent(json_encode($structure->generate($component)));
        } else {
            $result = (string) $component->render();
        }

        $this->_response->appendBody($result);
    }

    /**
     * Wrap content
     *
     * @param string $content
     * @return string
     */
    protected function wrapContent($content)
    {
        return '<script type="text/x-magento-init">{"*": {"Magento_Ui/js/core/app": ' . $content . '}}'
        . '</script>';
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
}
