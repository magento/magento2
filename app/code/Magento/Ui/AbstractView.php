<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Ui;

use Magento\Framework\View\Element\UiComponent\ConfigBuilderInterface;
use Magento\Framework\View\Element\UiComponent\ConfigFactory;
use Magento\Framework\View\Element\UiComponent\ConfigInterface;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Asset\Repository;
use Magento\Ui\ContentType\ContentTypeFactory;
use Magento\Framework\View\Element\Template\Context as TemplateContext;

/**
 * Abstract class AbstractView
 */
abstract class AbstractView extends Template implements UiComponentInterface
{
    /**
     * @var ConfigBuilderInterface
     */
    protected $configurationBuilder;

    /**
     * Root view component
     *
     * @var UiComponentInterface
     */
    protected $rootComponent;

    /**
     * View configuration data
     *
     * @var ConfigInterface
     */
    protected $configuration;

    /**
     * Render context
     *
     * @var Context
     */
    protected $renderContext;

    /**
     * @var ConfigFactory
     */
    protected $configurationFactory;

    /**
     * Content type factory
     *
     * @var ContentTypeFactory
     */
    protected $contentTypeFactory;

    /**
     * Asset service
     *
     * @var Repository
     */
    protected $assetRepo;

    /**
     * Constructor
     *
     * @param TemplateContext $context
     * @param Context $renderContext
     * @param ContentTypeFactory $contentTypeFactory
     * @param ConfigFactory $configFactory
     * @param ConfigBuilderInterface $configBuilder
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Context $renderContext,
        ContentTypeFactory $contentTypeFactory,
        ConfigFactory $configFactory,
        ConfigBuilderInterface $configBuilder,
        array $data = []
    ) {
        $this->renderContext = $renderContext;
        $this->contentTypeFactory = $contentTypeFactory;
        $this->assetRepo = $context->getAssetRepository();
        $this->configurationFactory = $configFactory;
        $this->configurationBuilder = $configBuilder;
        parent::__construct($context, $data);
    }

    /**
     * Update data
     *
     * @param array $arguments
     * @return void
     */
    public function update(array $arguments = [])
    {
        if ($arguments) {
            $this->_data = array_merge_recursive($this->_data, $arguments);
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
     * @return string
     */
    public function render()
    {
        $result = $this->contentTypeFactory->get($this->renderContext->getAcceptType())
            ->render($this, $this->getContentTemplate());
        return $result;
    }

    /**
     * Render label
     *
     * @return mixed|string
     */
    public function renderLabel()
    {
        $result = $this->contentTypeFactory->get($this->renderContext->getAcceptType())
            ->render($this, $this->getLabelTemplate());
        return $result;
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
        $element = $this->renderContext->getRender()->createUiComponent($elementName);
        $prevData = $element->getData();
        $element->update($arguments);
        $result = $element->render();
        $element->setData($prevData);
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
        $element = $this->renderContext->getRender()->createUiComponent($elementName);
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
     * @return ContentType\ContentTypeInterface
     */
    protected function getRenderEngine()
    {
        return $this->contentTypeFactory->get($this->renderContext->getAcceptType());
    }

    /**
     * Get name component instance
     *
     * @return string
     */
    public function getName()
    {
        return $this->configuration->getName();
    }

    /**
     * Get parent name component instance
     *
     * @return string
     */
    public function getParentName()
    {
        return $this->configuration->getParentName();
    }

    /**
     * Get configuration builder
     *
     * @return ConfigBuilderInterface
     */
    public function getConfigurationBuilder()
    {
        return $this->configurationBuilder;
    }

    /**
     * Get component configuration
     *
     * @return ConfigInterface
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Get render context
     *
     * @return Context
     */
    public function getRenderContext()
    {
        return $this->renderContext;
    }
}
