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
 * Class Field
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
        $dataType = $this->getData('config/dataType');
        if ($dataType) {
            $this->wrappedComponent = $this->uiComponentFactory->create(
                $this->getName(),
                $dataType,
                ['context' => $this->getContext()]
            );
            $this->wrappedComponent->prepare();
            $jsConfig = $this->getJsConfiguration($this->wrappedComponent);
            $this->getContext()->addComponentDefinition($this->wrappedComponent->getComponentName(), $jsConfig);
        }
    }

    /**
     * Get JS config
     *
     * @return array
     */
    public function getJsConfig()
    {
        return $this->wrappedComponent->getJsConfig();
    }
}
