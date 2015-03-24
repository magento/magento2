<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form;

use Magento\Ui\Component\AbstractComponent;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Fieldset
 */
class Fieldset extends AbstractComponent
{
    const NAME = 'fieldset';

    /**
     * @var bool
     */
    protected $collapsible = false;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->uiComponentFactory = $uiComponentFactory;
        parent::__construct($context, $components, $data);
    }


    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();

        $fieldsMeta = $this->getContext()->getDataProvider()->getFieldsMetaInfo($this->getName());
        foreach ($fieldsMeta as $name => $fieldData) {
            if (empty($fieldData)) {
                continue;
            }
            $fieldComponent = isset($this->components[$name]) ? $this->components[$name] : null;
            if ($fieldComponent === null) {
                $fieldData = $this->updateDataScope($fieldData, $name);
                $argument = [
                    'context' => $this->getContext(),
                    'data' => [
                        'name' => $name,
                        'config' => $fieldData
                    ]
                ];
                $fieldComponent = $this->uiComponentFactory->create($name, 'field', $argument);
                $fieldComponent->prepare();
                $this->components[$name] = $fieldComponent;
            } else {
                $config = $fieldComponent->getData('config');
                $config = array_replace_recursive($config, $fieldData);
                $config = $this->updateDataScope($config, $fieldComponent->getName());
                $fieldComponent->setData('config', $config);
            }
        }

        $jsConfig = $this->getConfiguration($this);
        $this->getContext()->addComponentDefinition($this->getComponentName(), $jsConfig);
    }

    /**
     * Update DataScope
     *
     * @param array $data
     * @param string $name
     * @return array
     */
    public function updateDataScope(array $data, $name)
    {
        if (!isset($data['dataScope'])) {
            $data['dataScope'] = $name;
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getLegendText()
    {
        return $this->getData('config/label');
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsCollapsible()
    {
        return $this->getData('config/collapsible', $this->collapsible);
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getData('config/source');
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->getData('config/content');
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->getData('children');
    }
}
