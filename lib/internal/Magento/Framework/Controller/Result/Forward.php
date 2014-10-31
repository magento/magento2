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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Controller\Result;

use Magento\Framework\Controller\AbstractResult;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RequestInterface;

class Forward extends AbstractResult
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var string
     */
    protected $module;

    /**
     * @var string
     */
    protected $controller;

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @param string $module
     * @return $this
     */
    public function setModule($module)
    {
        $this->module = $module;
        return $this;
    }

    /**
     * @param string $controller
     * @return $this
     */
    public function setController($controller)
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @param string $action
     * @return $this
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
     */
    protected function render(ResponseInterface $response)
    {
        return $this;
    }
}
