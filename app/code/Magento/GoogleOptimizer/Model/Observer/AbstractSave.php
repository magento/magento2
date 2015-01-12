<?php
/**
 * Google Experiment Abstract Save observer
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Model\Observer;

use Magento\Framework\Event\Observer;

abstract class AbstractSave
{
    /**
     * @var \Magento\GoogleOptimizer\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\GoogleOptimizer\Model\Code
     */
    protected $_modelCode;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var array
     */
    protected $_params;

    /**
     * @param \Magento\GoogleOptimizer\Helper\Data $helper
     * @param \Magento\GoogleOptimizer\Model\Code $modelCode
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\GoogleOptimizer\Helper\Data $helper,
        \Magento\GoogleOptimizer\Model\Code $modelCode,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_helper = $helper;
        $this->_modelCode = $modelCode;
        $this->_request = $request;
    }

    /**
     * Save script after saving entity
     *
     * @param Observer $observer
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function saveGoogleExperimentScript($observer)
    {
        $this->_initEntity($observer);

        if ($this->_isGoogleExperimentActive()) {
            $this->_processCode();
        }

        return $this;
    }

    /**
     * Init entity
     *
     * @param Observer $observer
     * @return void
     */
    abstract protected function _initEntity($observer);

    /**
     * Check is Google Experiment enabled
     *
     * @return bool
     */
    protected function _isGoogleExperimentActive()
    {
        return $this->_helper->isGoogleExperimentActive();
    }

    /**
     * Processes Save event of the entity
     *
     * @return void
     */
    protected function _processCode()
    {
        $this->_initRequestParams();

        if ($this->_isNewCode()) {
            $this->_saveCode();
        } else {
            $this->_loadCode();
            if ($this->_isEmptyCode()) {
                $this->_deleteCode();
            } else {
                $this->_saveCode();
            }
        }
    }

    /**
     * Init request params
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function _initRequestParams()
    {
        $params = $this->_request->getParam('google_experiment');
        if (!is_array($params) || !isset($params['experiment_script']) || !isset($params['code_id'])) {
            throw new \InvalidArgumentException('Wrong request parameters');
        }
        $this->_params = $params;
    }

    /**
     * Check is new model
     *
     * @return bool
     */
    protected function _isNewCode()
    {
        return empty($this->_params['code_id']);
    }

    /**
     * Save code model
     *
     * @return void
     */
    protected function _saveCode()
    {
        $this->_modelCode->addData($this->_getCodeData());
        $this->_modelCode->save();
    }

    /**
     * Get data for saving code model
     *
     * @return array
     */
    abstract protected function _getCodeData();

    /**
     * Load model code
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function _loadCode()
    {
        $this->_modelCode->load($this->_params['code_id']);
        if (!$this->_modelCode->getId()) {
            throw new \InvalidArgumentException('Code does not exist');
        }
    }

    /**
     * Is empty code
     *
     * @return bool
     */
    protected function _isEmptyCode()
    {
        return empty($this->_params['experiment_script']);
    }

    /**
     * Delete model code
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function _deleteCode()
    {
        $this->_modelCode->delete();
    }
}
