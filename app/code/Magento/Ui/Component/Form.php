<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Framework\View\Element\UiComponent\ConfigBuilderInterface;
use Magento\Framework\View\Element\UiComponent\ConfigFactory;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiElementFactory;
use Magento\Ui\Component\Control\ActionPool;
use Magento\Ui\Component\Control\ButtonProviderFactory;
use Magento\Ui\Component\Control\ButtonProviderInterface;
use Magento\Ui\ContentType\ContentTypeFactory;
use Magento\Ui\DataProvider\Factory as DataProviderFactory;
use Magento\Ui\DataProvider\Manager;

/**
 * Class Form
 */
class Form extends AbstractView
{
    /**
     * Default form element
     */
    const DEFAULT_FORM_ELEMENT = 'input';

    /**
     * From element map
     *
     * @var array
     */
    protected $formElementMap = [
        'text' => 'input',
        'number' => 'input',
    ];

    /**
     * Ui element builder
     *
     * @var ElementRendererBuilder
     */
    protected $elementRendererBuilder;

    /**
     * @var UiElementFactory
     */
    protected $factory;

    /**
     * @var ActionPool
     */
    protected $actionPool;

    /**
     * @var ButtonProviderFactory
     */
    protected $buttonProviderFactory;

    /**
     * Constructor
     *
     * @param TemplateContext $context
     * @param Context $renderContext
     * @param ContentTypeFactory $contentTypeFactory
     * @param ConfigFactory $configFactory
     * @param ConfigBuilderInterface $configBuilder
     * @param DataProviderFactory $dataProviderFactory
     * @param Manager $dataProviderManager
     * @param ElementRendererBuilder $elementRendererBuilder
     * @param UiElementFactory $factory
     * @param ActionPool $actionPool
     * @param ButtonProviderFactory $buttonProviderFactory
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Context $renderContext,
        ContentTypeFactory $contentTypeFactory,
        ConfigFactory $configFactory,
        ConfigBuilderInterface $configBuilder,
        DataProviderFactory $dataProviderFactory,
        Manager $dataProviderManager,
        ElementRendererBuilder $elementRendererBuilder,
        UiElementFactory $factory,
        ActionPool $actionPool,
        ButtonProviderFactory $buttonProviderFactory,
        array $data = []
    ) {
        $this->elementRendererBuilder = $elementRendererBuilder;
        $this->factory = $factory;
        $this->actionPool = $actionPool;
        $this->buttonProviderFactory = $buttonProviderFactory;
        parent::__construct(
            $context,
            $renderContext,
            $contentTypeFactory,
            $configFactory,
            $configBuilder,
            $dataProviderFactory,
            $dataProviderManager,
            $data
        );
    }

    /**
     * Prepare component data
     *
     * @return void
     */
    public function prepare()
    {
        $this->registerComponents();
        $buttons = $this->getData('buttons');
        if ($buttons) {
            foreach ($buttons as $buttonId => $buttonClass) {
                /** @var ButtonProviderInterface $button */
                $button = $this->buttonProviderFactory->create($buttonClass);
                $buttonData = $button->getButtonData();
                if (!$buttonData) {
                    unset($buttons[$buttonId]);
                    continue;
                }
                $buttons[$buttonId] = $buttonData;
            }
            uasort($buttons, [$this, 'sortButtons']);
            foreach ($buttons as $buttonId => $buttonData) {
                $this->actionPool->add($buttonId, $buttonData, $this);
            }
        }

        $layoutSettings = (array) $this->getData('layout');
        $data = [
            'name' => $this->getData('name'),
            'label' => $this->getData('label'),
            'data_sources' => $this->getData('data_sources'),
            'child_blocks' => $this->getLayout()->getChildBlocks($this->getNameInLayout()),
            'configuration' => isset($layoutSettings['configuration'])
                ? $layoutSettings['configuration']
                : [],
        ];
        $layoutType = isset($layoutSettings['type'])
            ? $layoutSettings['type']
            : \Magento\Ui\Component\Layout\Tabs::NAME;
        $layout = $this->factory->create(
            $layoutType,
            $data
        );
        $layout->prepare();
        $this->elements[] = $layout;
    }

    /**
     * @return string
     */
    public function getDataScope()
    {
        return $this->getData('name');
    }

    /**
     * Register all UI Components configuration
     *
     * @return void
     */
    protected function registerComponents()
    {
        $this->renderContext->getStorage()->addComponent(
            $this->getData('name'),
            [
                'component' => 'Magento_Ui/js/form/component',
                'config' => [
                    'provider' => $this->getData('name'),
                ],
                'deps' => [$this->getData('name')]
            ]
        );
        foreach ($this->getLayout()->getAllBlocks() as $name => $block) {
            if ($block instanceof \Magento\Framework\View\Element\UiComponentInterface) {
                $config = (array)$block->getData('js_config');
                if (!isset($config['extends'])) {
                    $config['extends'] = $this->getData('name');
                }
                $this->renderContext->getStorage()->addComponent($name, $config);
            }
        };
    }

    /**
     * @return string
     */
    public function getSaveAction()
    {
        return $this->getUrl('mui/form/save');
    }

    /**
     * @return string
     */
    public function getValidateAction()
    {
        return $this->getUrl('mui/form/validate');
    }

    /**
     * Sort buttons by sort order
     *
     * @param array $itemA
     * @param array $itemB
     * @return int
     */
    public function sortButtons(array $itemA, array $itemB)
    {
        $sortOrderA = isset($itemA['sort_order']) ? intval($itemA['sort_order']) : 0;
        $sortOrderB = isset($itemB['sort_order']) ? intval($itemB['sort_order']) : 0;
        if ($sortOrderA == $sortOrderB) {
            return 0;
        }
        return ($sortOrderA < $sortOrderB) ? -1 : 1;
    }
}
