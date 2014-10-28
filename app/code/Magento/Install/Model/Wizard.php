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

/**
 * Installation wizard model
 */
namespace Magento\Install\Model;

use Magento\Framework\UrlInterface;

class Wizard
{
    /**
     * Wizard configuration
     *
     * @var array
     */
    protected $_steps = array();

    /**
     * Url builder
     *
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * Init install wizard
     * @param UrlInterface $urlBuilder
     * @param Config $installConfig
     */
    public function __construct(UrlInterface $urlBuilder, Config $installConfig)
    {
        $this->_steps = $installConfig->getWizardSteps();
        $this->_urlBuilder = $urlBuilder;
        $this->_initSteps();
    }

    /**
     * @return void
     */
    protected function _initSteps()
    {
        foreach (array_keys($this->_steps) as $index) {
            $this->_steps[$index]->setUrl(
                $this->_getUrl($this->_steps[$index]->getController(), $this->_steps[$index]->getAction())
            );

            if (isset($this->_steps[$index + 1])) {
                $this->_steps[$index]->setNextUrl(
                    $this->_getUrl($this->_steps[$index + 1]->getController(), $this->_steps[$index + 1]->getAction())
                );
                $this->_steps[$index]->setNextUrlPath(
                    $this->_getUrlPath(
                        $this->_steps[$index + 1]->getController(),
                        $this->_steps[$index + 1]->getAction()
                    )
                );
            }
            if (isset($this->_steps[$index - 1])) {
                $this->_steps[$index]->setPrevUrl(
                    $this->_getUrl($this->_steps[$index - 1]->getController(), $this->_steps[$index - 1]->getAction())
                );
                $this->_steps[$index]->setPrevUrlPath(
                    $this->_getUrlPath(
                        $this->_steps[$index - 1]->getController(),
                        $this->_steps[$index - 1]->getAction()
                    )
                );
            }
        }
    }

    /**
     * Get wizard step by request
     *
     * @param   \Magento\Framework\App\RequestInterface $request
     * @return  \Magento\Framework\Object|bool
     */
    public function getStepByRequest(\Magento\Framework\App\RequestInterface $request)
    {
        foreach ($this->_steps as $step) {
            if ($step->getController() == $request->getControllerName() &&
                $step->getAction() == $request->getActionName()
            ) {
                return $step;
            }
        }
        return false;
    }

    /**
     * Get wizard step by name
     *
     * @param   string $name
     * @return  \Magento\Framework\Object|bool
     */
    public function getStepByName($name)
    {
        foreach ($this->_steps as $step) {
            if ($step->getName() == $name) {
                return $step;
            }
        }
        return false;
    }

    /**
     * Get all wizard steps
     *
     * @return array
     */
    public function getSteps()
    {
        return $this->_steps;
    }

    /**
     * Get url
     *
     * @param string $controller
     * @param string $action
     * @return string
     */
    protected function _getUrl($controller, $action)
    {
        return $this->_urlBuilder->getUrl($this->_getUrlPath($controller, $action));
    }

    /**
     * Retrieve Url Path
     *
     * @param string $controller
     * @param string $action
     * @return string
     */
    protected function _getUrlPath($controller, $action)
    {
        return 'install/' . $controller . '/' . $action;
    }
}
