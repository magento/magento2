<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\UiComponentInterface;
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
     * Add external data to data provider
     *
     * Can be usefull when we need to inject common data for few instances of UI components
     *
     * @param UiComponentInterface $uiComponent
     * @param array $widgetData
     * @return void
     */
    private function injectDataInDataSource(UiComponentInterface $uiComponent, array $widgetData)
    {
        $context = $uiComponent->getContext();
        $configData = $context->getDataProvider()->getConfigData();
        $context->getDataProvider()
            ->setConfigData(
                array_replace($configData, $widgetData)
            );
    }

    /**
     * This is a second way to configure Ui component on a fly, with different entire data
     * Instead of DataProvider it allows to launch few instances of one Ui Component on one page, depend
     * on entire data
     *
     * @param UiComponentInterface $uiComponent
     * @param array $data
     * @return void
     */
    private function addDataToChildComponents(UiComponentInterface $uiComponent, array $data)
    {
        foreach ($uiComponent->getChildComponents() as $childComponent) {
            if (isset($data[$childComponent->getName()]) && is_array($data[$childComponent->getName()])) {
                $childComponent->setData(
                    'config',
                    array_replace_recursive(
                        $childComponent->getData('config'),
                        $data[$childComponent->getName()]
                    )
                );
            }

            $this->addDataToChildComponents($childComponent, $data);
        }
    }

    /**
     * Create and render ui Component
     * Additional settings and data can be provided in this method
     * This data will be merged and can be used on store front with according ui component.
     *
     * @param array $data -> data, that can be injected to data source or to child components
     * @return string
     */
    public function renderApp($data = [])
    {
        /** @var \Magento\Ui\Component\AbstractComponent $uiComponent */
        $uiComponent = $this->uiComponentGenerator
            ->generateUiComponent($this->getData('uiComponent'), $this->getLayout());
        $this->injectDataInDataSource($uiComponent, $this->getData());
        $this->addDataToChildComponents($uiComponent, $data);
        return (string) $uiComponent->render();
    }
}
