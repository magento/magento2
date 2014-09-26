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
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponentFactory;

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
        $this->layout = $layout;
    }

    /**
     * Get root layout
     *
     * @return LayoutInterface
     */
    public function getPageLayout()
    {
        return $this->layout;
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
    public function getConfigurationBuilder()
    {
        return $this->configStorageBuilder;
    }
}
