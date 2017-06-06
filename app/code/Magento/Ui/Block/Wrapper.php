<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Block;

use Magento\Framework\View\Element\Template;
use Magento\Ui\Model\UiComponentGenerator;

/**
 * This block is wrapper for UI component, this done in order to save compatability with old
 * widgets mechanism
 */
class Wrapper extends \Magento\Framework\View\Element\Template
{
    /**
     * @var UiComponentGenerator
     */
    private $uiComponentGenerator;

    /**
     * Wrapper constructor.
     * @param Template\Context $context
     * @param UiComponentGenerator $uiComponentGenerator
     * @param array $data
     */
    public function __construct(Template\Context $context, UiComponentGenerator $uiComponentGenerator, array $data = [])
    {
        parent::__construct($context, $data);
        $this->uiComponentGenerator = $uiComponentGenerator;
    }

    /**
     * Create and render ui Component
     * Additional settings and data can be provided in this method
     * This data will be merged and can be used on store front with according ui component.
     *
     * @param array $data
     * @return string
     */
    public function renderApp($data = [])
    {
        $widgetData = array_replace_recursive($this->getData(), $data);
        /** @var \Magento\Ui\Component\AbstractComponent $uiComponent */
        $uiComponent = $this->uiComponentGenerator
            ->generateUiComponent($this->getData('uiComponent'), $this->getLayout());
        $context = $uiComponent->getContext();
        $configData = $context->getDataProvider()->getConfigData();
        $context->getDataProvider()
            ->setConfigData(
                array_replace($configData, $widgetData)
            );
        return (string) $uiComponent->render();
    }
}
