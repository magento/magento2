<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Ui\DataProvider\Manager;
use Magento\Framework\View\Element\Template;
use \Magento\Ui\Component\Control\ActionPoolInterface;
use Magento\Ui\Component\Control\ButtonProviderFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Control\ButtonProviderInterface;
use Magento\Framework\View\Element\UiComponent\ArrayObjectFactory;
use Magento\Framework\View\Element\UiComponent\ContentType\ContentTypeFactory;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Framework\View\Element\UiComponent\Context as RenderContext;

/**
 * Abstract class AbstractView
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractView extends Template implements UiComponentInterface
{
    /**
     * Render context
     *
     * @var RenderContext
     */
    protected $renderContext;

    /**
     * Content type factory
     *
     * @var ContentTypeFactory
     */
    protected $contentTypeFactory;

    /**
     * @var Manager
     */
    protected $dataManager;

    /**
     * Layouts for the render
     *
     * @var UiComponentInterface
     */
    protected $uiLayout;

    /**
     * @var \ArrayObject
     */
    protected $componentData;

    /**
     * @var ButtonProviderFactory
     */
    protected $buttonProviderFactory;

    /**
     * @var ActionPoolInterface
     */
    protected $actionPool;

    /**
     * Constructor
     *
     * @param TemplateContext $context
     * @param RenderContext $renderContext
     * @param ContentTypeFactory $contentTypeFactory
     * @param Manager $dataProviderManager
     * @param ArrayObjectFactory $arrayObjectFactory
     * @param ButtonProviderFactory $buttonProviderFactory
     * @param ActionPoolInterface $actionPool
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        RenderContext $renderContext,
        ContentTypeFactory $contentTypeFactory,
        Manager $dataProviderManager,
        ArrayObjectFactory $arrayObjectFactory,
        ButtonProviderFactory $buttonProviderFactory,
        ActionPoolInterface $actionPool,
        array $data = []
    ) {
        $this->renderContext = $renderContext;
        $this->contentTypeFactory = $contentTypeFactory;
        $this->dataManager = $dataProviderManager;
        $this->componentData = $arrayObjectFactory->create();
        $this->actionPool = $actionPool;
        $this->buttonProviderFactory = $buttonProviderFactory;

        parent::__construct($context, $data);
    }

    /**
     * Update data
     *
     * @param array $data
     * @return void
     */
    public function update(array $data = [])
    {
        if (!empty($data)) {
            $this->_data = array_merge_recursive($this->_data, $data);
        }
    }

    /**
     * Prepare component data
     *
     * @return void
     */
    public function prepare()
    {
        //
    }

    /**
     * Render content
     *
     * @param array $data
     * @return string
     */
    public function render(array $data = [])
    {
        $prevData = $this->getData();
        $this->update($data);

        $renderResult = $this->getRenderEngine()->render($this, $this->getContentTemplate());

        $this->setData($prevData);

        return $renderResult;
    }

    /**
     * Render label
     *
     * @return mixed|string
     */
    public function renderLabel()
    {
        return $this->getRenderEngine()->render($this, $this->getLabelTemplate());
    }

    /**
     * Render element
     *
     * @param string $elementName
     * @param array $arguments
     * @return mixed|string
     */
    public function renderElement($elementName, array $arguments)
    {
        $element = $this->renderContext->getRender()->getUiElementView($elementName);
        $result = $element->render($arguments);

        return $result;
    }

    /**
     * Render component label
     *
     * @param string $elementName
     * @param array $arguments
     * @return string
     */
    public function renderElementLabel($elementName, array $arguments)
    {
        $element = $this->renderContext->getRender()->getUiElementView($elementName);
        $prevData = $element->getData();
        $element->update($arguments);
        $result = $element->renderLabel();
        $element->setData($prevData);

        return $result;
    }

    /**
     * Shortcut for rendering as HTML
     * (used for backward compatibility with standard rendering mechanism via layout interface)
     *
     * @return string
     */
    public function toHtml()
    {
        return $this->render();
    }

    /**
     * Getting label template
     *
     * @return string|false
     */
    public function getLabelTemplate()
    {
        return 'Magento_Ui::label/default.phtml';
    }

    /**
     * Getting content template
     *
     * @return string|false
     */
    public function getContentTemplate()
    {
        return $this->getData('content_template');
    }

    /**
     * Get Layout Node
     *
     * @param string $fullName
     * @param mixed $default
     * @return array
     */
    public function getLayoutElement($fullName, $default = null)
    {
        return $this->renderContext->getStorage()->getLayoutNode($fullName, $default);
    }

    /**
     * Get name component instance
     *
     * @return string|null
     */
    public function getName()
    {
        return isset($this->componentData['config']['name']) ? $this->componentData['config']['name'] : null;
    }

    /**
     * Get parent name component instance
     *
     * @return string|null
     */
    public function getParentName()
    {
        return isset($this->componentData['config']['parent_name'])
            ? $this->componentData['config']['parent_name']
            : null;
    }

    /**
     * Set component configuration
     *
     * @param string|null $name
     * @param string|null $parentName
     * @return void
     */
    public function prepareConfiguration($name = null, $parentName = null)
    {
        $defaultConfig = $this->getDefaultConfiguration();
        if ($this->hasData('config')) {
            $defaultConfig = array_merge($defaultConfig, $this->getData('config'));
        }
        $config = [];
        $config['name'] = $name ?: $this->renderContext->getNamespace() . '_' . $this->getNameInLayout();
        $config['parent_name'] = $parentName ?: $this->renderContext->getNamespace();
        if (!empty($defaultConfig)) {
            $config['configuration'] = $defaultConfig;
        }

        $this->componentData['config'] = $config;
    }

    /**
     * Get render context
     *
     * @return RenderContext
     */
    public function getRenderContext()
    {
        return $this->renderContext;
    }

    /**
     * Set layout for the render
     *
     * @return UiComponentInterface
     */
    public function getUiLayout()
    {
        return $this->uiLayout;
    }

    /**
     * Set layout for the render
     *
     * @param UiComponentInterface $uiLayout
     * @return void
     */
    public function setUiLayout(UiComponentInterface $uiLayout)
    {
        $this->uiLayout = $uiLayout;
    }

    /**
     * Component data
     *
     * @return \ArrayObject
     */
    public function getComponentData()
    {
        return $this->componentData;
    }

    /**
     * Get default parameters
     *
     * @return array
     */
    protected function getDefaultConfiguration()
    {
        return [];
    }

    /**
     * Get render engine
     *
     * @return \Magento\Ui\ContentType\ContentTypeInterface
     */
    protected function getRenderEngine()
    {
        return $this->contentTypeFactory->get($this->renderContext->getAcceptType());
    }

    /**
     * Add button in the actions toolbar
     *
     * @return void
     */
    protected function addButtons()
    {
        $buttons = $this->getData('buttons');
        if ($buttons) {
            foreach ($buttons as $buttonId => $buttonData) {
                if (is_array($buttonData)) {
                    $buttons[$buttonId] = $buttonData;
                    continue;
                }
                /** @var ButtonProviderInterface $button */
                $button = $this->buttonProviderFactory->create($buttonData);
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

        return $sortOrderA - $sortOrderB;
    }

    /**
     * Method is called before rendering
     *
     * @return void
     */
    public function beforeRender()
    {
        //
    }

    /**
     * Method is called after rendering
     *
     * @return void
     */
    public function afterRender()
    {
        //
    }

    /**
     * Get component instance name
     *
     * @return string
     */
    public function getComponentName()
    {
        //
    }
}
