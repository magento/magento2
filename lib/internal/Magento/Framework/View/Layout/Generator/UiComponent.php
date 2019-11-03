<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Layout\Generator;

use Magento\Framework\View\Element\BlockFactory;
use Magento\Framework\View\Element\UiComponent\ContainerInterface;
use Magento\Framework\View\Element\UiComponent\ContextFactory as UiComponentContextFactory;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Layout\Data\Structure as DataStructure;
use Magento\Framework\View\Layout\Element;
use Magento\Framework\View\Layout\Generator\Context as GeneratorContext;
use Magento\Framework\View\Layout\GeneratorInterface;
use Magento\Framework\View\Layout\Reader\Context as ReaderContext;
use Magento\Framework\View\LayoutInterface;

/**
 * Class UiComponent
 */
class UiComponent implements GeneratorInterface
{
    /**
     * Generator type
     */
    const TYPE = 'uiComponent';

    /**
     * Block container for components
     */
    const CONTAINER = \Magento\Framework\View\Element\UiComponent\ContainerInterface::class;

    /**
     * @var UiComponentFactory
     */
    protected $uiComponentFactory;

    /**
     * @var UiComponentContextFactory
     */
    protected $contextFactory;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * Constructor
     *
     * @param UiComponentFactory $uiComponentFactory
     * @param BlockFactory $blockFactory
     * @param UiComponentContextFactory $contextFactory
     */
    public function __construct(
        UiComponentFactory $uiComponentFactory,
        BlockFactory $blockFactory,
        UiComponentContextFactory $contextFactory
    ) {
        $this->uiComponentFactory = $uiComponentFactory;
        $this->blockFactory = $blockFactory;
        $this->contextFactory = $contextFactory;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * Creates UI Component object based on scheduled data and add it to the layout
     *
     * @param ReaderContext $readerContext
     * @param GeneratorContext $generatorContext
     * @return $this
     */
    public function process(ReaderContext $readerContext, GeneratorContext $generatorContext)
    {
        $scheduledStructure = $readerContext->getScheduledStructure();
        $scheduledElements = $scheduledStructure->getElements();
        if (!$scheduledElements) {
            return $this;
        }
        $structure = $generatorContext->getStructure();
        $layout = $generatorContext->getLayout();

        // Instantiate blocks and collect all actions data
        foreach ($scheduledElements as $elementName => $element) {
            list($elementType, $data) = $element;

            if ($elementType !== Element::TYPE_UI_COMPONENT) {
                continue;
            }

            $layout->setBlock(
                $elementName,
                $this->generateComponent($structure, $elementName, $data, $layout)
            );
            $scheduledStructure->unsetElement($elementName);
        }

        return $this;
    }

    /**
     * Create component object
     *
     * @param DataStructure $structure
     * @param string $elementName
     * @param string $data
     * @param LayoutInterface $layout
     * @return ContainerInterface
     */
    protected function generateComponent(DataStructure $structure, $elementName, $data, LayoutInterface $layout)
    {
        $attributes = $data['attributes'];
        if (!empty($attributes['group'])) {
            $structure->addToParentGroup($elementName, $attributes['group']);
        }

        $context = $this->contextFactory->create(
            [
                'namespace' => $elementName,
                'pageLayout' => $layout
            ]
        );

        /**
         * Structure is required for custom component factory like a 'htmlContent'
         */
        $component = $this->uiComponentFactory->create(
            $elementName,
            null,
            ['context' => $context, 'structure' => $structure]
        );
        $this->prepareComponent($component);

        /** @var ContainerInterface $blockContainer */
        $blockContainer = $this->blockFactory->createBlock(static::CONTAINER, ['component' => $component]);

        return $blockContainer;
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
