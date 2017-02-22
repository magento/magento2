<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form;

use Magento\Ui\Component\Container;
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
     * @var UiComponentInterface[]
     */
    protected $fieldsInContainers = [];

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
        foreach ($this->getChildComponents() as $name => $child) {
            if ($child instanceof Container) {
                $this->fieldsInContainers += $child->getChildComponents();
            }
        }
        $fieldsMeta = $this->getContext()->getDataProvider()->getFieldsMetaInfo($this->getName());
        foreach ($fieldsMeta as $name => $fieldData) {
            if (empty($fieldData)) {
                continue;
            }
            $fieldComponent = $this->getComponent($name);
            $this->prepareField($fieldData, $name, $fieldComponent);
        }
        parent::prepare();
    }

    /**
     * Prepare field component
     *
     * @param array $fieldData
     * @param string $name
     * @param UiComponentInterface|null $fieldComponent
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function prepareField(array $fieldData, $name, UiComponentInterface $fieldComponent = null)
    {
        if ($fieldComponent === null) {
            if (isset($this->fieldsInContainers[$name])) {
                $this->updateField($fieldData, $this->fieldsInContainers[$name]);
                return;
            }
            $fieldData = $this->updateDataScope($fieldData, $name);
            $argument = [
                'context' => $this->getContext(),
                'data' => [
                    'name' => $name,
                    'config' => $fieldData
                ]
            ];
            $fieldComponent = $this->uiComponentFactory->create($name, Field::NAME, $argument);
            $fieldComponent->prepare();
            $this->addComponent($name, $fieldComponent);
        } else {
            $this->updateField($fieldData, $fieldComponent);
        }
    }

    /**
     * Update field data
     *
     * @param array $fieldData
     * @param UiComponentInterface $component
     * @return void
     */
    protected function updateField(array $fieldData, UiComponentInterface $component)
    {
        $config = $component->getData('config');
        // XML data configuration override configuration coming from the DB
        $config = array_replace_recursive($fieldData, $config);
        $config = $this->updateDataScope($config, $component->getName());
        $component->setData('config', $config);
    }

    /**
     * Update DataScope
     *
     * @param array $data
     * @param string $name
     * @return array
     */
    protected function updateDataScope(array $data, $name)
    {
        if (!isset($data['dataScope'])) {
            $data['dataScope'] = $name;
        }
        return $data;
    }
}
