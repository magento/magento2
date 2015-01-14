<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Abstract Validation State Model
 */
namespace Magento\Centinel\Model;

abstract class AbstractState extends \Magento\Framework\Object
{
    /**
     * Storage data model
     *
     * @var \Magento\Framework\Object
     */
    private $_dataStorage = false;

    /**
     * Setter for storage data model
     *
     * @param \Magento\Framework\Object $dataStorageModel
     * @return $this
     */
    public function setDataStorage($dataStorageModel)
    {
        $this->_dataStorage = $dataStorageModel;
        return $this;
    }

    /**
     * Getter for storage data model
     *
     * @return \Magento\Framework\Object
     */
    public function getDataStorage()
    {
        return $this->_dataStorage;
    }

    /**
     * Retrieves data from the object
     *
     * If $key is empty will return all the data as an array
     * Otherwise it will return value of the attribute specified by $key
     *
     * $index parameter is ignored
     *
     * @param string $key
     * @param string|int $index
     * @return mixed
     * @see \Magento\Framework\Session\SessionManager::getData()
     */
    public function getData($key = '', $index = null)
    {
        return $this->getDataStorage()->getData($key);
    }

    /**
     * Overwrite data in the object.
     *
     * Parameter $key can be string or array.
     * If $key is string, the attribute value will be overwritten by $value
     *
     * If $key is an array, it will overwrite all the data in the object.
     *
     * @param string|array $key
     * @param mixed $value
     * @return $this
     */
    public function setData($key, $value = null)
    {
        $this->getDataStorage()->setData($key, $value);
        return $this;
    }

    /**
     * Save lookup result in state model
     *
     * @param \Magento\Framework\Object $result
     * @return $this
     */
    public function setLookupResult($result)
    {
        foreach ($result->getData() as $key => $value) {
            $this->setData('lookup_' . $key, $value);
        }
        return $this;
    }

    /**
     * Save authenticate result in state model
     *
     * @param \Magento\Framework\Object $result
     * @return $this
     */
    public function setAuthenticateResult($result)
    {
        foreach ($result->getData() as $key => $value) {
            $this->setData('authenticate_' . $key, $value);
        }
        return $this;
    }

    /**
     * Analyse lookup`s results. If lookup is successful return true and false if it failure
     * Result depends from flag self::getIsModeStrict()
     *
     * @return bool
     */
    final public function isLookupSuccessful()
    {
        if ($this->_isLookupStrictSuccessful()) {
            return true;
        } elseif (!$this->getIsModeStrict() && $this->_isLookupSoftSuccessful()) {
            return true;
        }
        return false;
    }

    /**
     * Analyse lookup`s results. If lookup is strict successful return true
     *
     * @return bool
     */
    abstract protected function _isLookupStrictSuccessful();

    /**
     * Analyse lookup`s results. If lookup is soft successful return true
     *
     * @return bool
     */
    abstract protected function _isLookupSoftSuccessful();

    /**
     * Analyse lookup`s results. If it has require params for authenticate, return true
     *
     * @return bool
     */
    abstract public function isAuthenticateAllowed();

    /**
     * Analyse authenticate`s results. If authenticate is successful return true and false if it failure
     * Result depends from flag self::getIsModeStrict()
     *
     * @return bool
     */
    abstract public function isAuthenticateSuccessful();
}
