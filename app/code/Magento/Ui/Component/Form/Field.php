<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Form;

use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\AbstractComponent;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Field
 * @api
 */
class Field extends AbstractComponent
{
    const NAME = 'field';

    /**
     * Wrapped component
     *
     * @var UiComponentInterface
     */
    protected $wrappedComponent;

    /**
     * UI component factory
     *
     * @var UiComponentFactory
     */
    protected $uiComponentFactory;

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
        return 'form.' . $this->wrappedComponent->getComponentName();
    }

    /**
     * Prepare component configuration
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepare()
    {
        $formElement = $this->getData('config/formElement');
        if (null === $formElement) {
            throw new LocalizedException(__(
                'The configuration parameter "formElement" is a required for "%1" field.',
                $this->getName()
            ));
        }
        // Create of wrapped component
        $this->wrappedComponent = $this->uiComponentFactory->create(
            $this->getName(),
            $formElement,
            array_merge(['context' => $this->getContext()], (array)$this->getData())
        );
        $this->wrappedComponent->setData(
            'config',
            array_replace_recursive(
                ['dataScope' => $this->getName()],
                (array) $this->wrappedComponent->getData('config'),
                (array) $this->getData('config')
            )
        );

        foreach ($this->components as $nameComponent => $component) {
            $this->wrappedComponent->addComponent($nameComponent, $component);
        }
        $this->prepareChildComponent($this->wrappedComponent);

        $this->components = $this->wrappedComponent->getChildComponents();
        // Merge JS configuration with wrapped component configuration
        $wrappedComponentConfig = $this->getJsConfig($this->wrappedComponent);

        $jsConfig = array_replace_recursive($wrappedComponentConfig, $this->getJsConfig($this));
        $jsConfig['extends'] = $this->wrappedComponent->getComponentName();
        $this->setData('js_config', $jsConfig);

        $this->setData('config', $this->wrappedComponent->getData('config'));

        parent::prepare();
    }
}
