<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\LayoutInterface;

/**
 * Class Context
 */
class Context extends Registry
{
    /**
     * Configuration storage builder
     *
     * @var ConfigStorageBuilderInterface
     */
    protected $configStorageBuilder;

    /**
     * Configuration storage
     *
     * @var ConfigStorageInterface
     */
    protected $configStorage;

    /**
     * Application request
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * Accept type
     *
     * @var string
     */
    protected $acceptType;

    /**
     * @var LayoutInterface
     */
    protected $pageLayout;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var UiComponentFactory
     */
    protected $factory;

    /**
     * Data Namespace
     *
     * @var string
     */
    protected $namespace;

    /**
     * Constructor
     *
     * @param ConfigStorageInterface $configStorage
     * @param ConfigStorageBuilderInterface $configStorageBuilder
     * @param RequestInterface $request
     */
    public function __construct(
        ConfigStorageInterface $configStorage,
        ConfigStorageBuilderInterface $configStorageBuilder,
        RequestInterface $request
    ) {
        $this->configStorage = $configStorage;
        $this->configStorageBuilder = $configStorageBuilder;
        $this->request = $request;
        $this->setAcceptType();
    }

    /**
     * Getting requested accept type
     *
     * @return void
     */
    protected function setAcceptType()
    {
        $this->acceptType = 'xml';

        $rawAcceptType = $this->request->getHeader('Accept');
        if (strpos($rawAcceptType, 'json') !== false) {
            $this->acceptType = 'json';
        } elseif (strpos($rawAcceptType, 'html') !== false) {
            $this->acceptType = 'html';
        }
    }

    /**
     * Getting accept type
     *
     * @return string
     */
    public function getAcceptType()
    {
        return $this->acceptType;
    }

    /**
     * Set Ui Components Factory
     *
     * @param UiComponentFactory $render
     * @return void
     */
    public function setRender(UiComponentFactory $render)
    {
        $this->factory = $render;
    }

    /**
     * Get Ui Components Factory
     *
     * @return UiComponentFactory
     */
    public function getRender()
    {
        return $this->factory;
    }

    /**
     * Set root layout
     *
     * @param LayoutInterface $layout
     * @return void
     */
    public function setPageLayout(LayoutInterface $layout)
    {
        $this->pageLayout = $layout;
    }

    /**
     * Get root layout
     *
     * @return LayoutInterface
     */
    public function getPageLayout()
    {
        return $this->pageLayout;
    }

    /**
     * Set root view
     *
     * @param string $namespace
     * @return void
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * Get root view
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Getting all request data
     *
     * @return mixed
     */
    public function getRequestParams()
    {
        return $this->request->getParams();
    }

    /**
     * Getting data according to the key
     *
     * @param string $key
     * @param mixed|null $defaultValue
     * @return mixed
     */
    public function getRequestParam($key, $defaultValue = null)
    {
        return $this->request->getParam($key, $defaultValue);
    }

    /**
     * Get storage configuration
     *
     * @return ConfigStorageInterface
     */
    public function getStorage()
    {
        return $this->configStorage;
    }

    /**
     * Get configuration builder
     *
     * @return ConfigStorageBuilderInterface
     */
    public function getConfigBuilder()
    {
        return $this->configStorageBuilder;
    }

    /**
     * @param LayoutInterface $layout
     * @return void
     */
    public function setLayout(LayoutInterface $layout)
    {
        $this->layout = $layout;
    }

    /**
     * @return LayoutInterface
     */
    public function getLayout()
    {
        return $this->layout;
    }
}
