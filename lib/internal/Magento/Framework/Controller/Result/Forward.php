<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Controller\Result;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;
use Magento\Framework\Controller\AbstractResult;

/**
 * Class \Magento\Framework\Controller\Result\Forward
 *
 * @since 2.0.0
 */
class Forward extends AbstractResult
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     * @since 2.0.0
     */
    protected $request;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $module;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $controller;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $params = [];

    /**
     * @param RequestInterface $request
     * @since 2.0.0
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @param string $module
     * @return $this
     * @since 2.0.0
     */
    public function setModule($module)
    {
        $this->module = $module;
        return $this;
    }

    /**
     * @param string $controller
     * @return $this
     * @since 2.0.0
     */
    public function setController($controller)
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * @param array $params
     * @return $this
     * @since 2.0.0
     */
    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @param string $action
     * @return $this
     * @since 2.0.0
     */
    public function forward($action)
    {
        $this->request->initForward();

        if (!empty($this->params)) {
            $this->request->setParams($this->params);
        }

        if (!empty($this->controller)) {
            $this->request->setControllerName($this->controller);

            // Module should only be reset if controller has been specified
            if (!empty($this->module)) {
                $this->request->setModuleName($this->module);
            }
        }

        $this->request->setActionName($action);
        $this->request->setDispatched(false);
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function render(HttpResponseInterface $response)
    {
        return $this;
    }
}
