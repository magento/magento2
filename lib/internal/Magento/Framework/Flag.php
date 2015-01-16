<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

/**
 * Flag model
 *
 * @method \Magento\Framework\Flag\Resource _getResource()
 * @method \Magento\Framework\Flag\Resource getResource()
 * @method string getFlagCode()
 * @method \Magento\Framework\Flag setFlagCode(string $value)
 * @method int getState()
 * @method \Magento\Framework\Flag setState(int $value)
 * @method string getLastUpdate()
 * @method \Magento\Framework\Flag setLastUpdate(string $value)
 */
class Flag extends Model\AbstractModel
{
    /**
     * Flag code
     *
     * @var string
     */
    protected $_flagCode = null;

    /**
     * Init resource model
     * Set flag_code if it is specified in arguments
     *
     * @return void
     */
    protected function _construct()
    {
        if ($this->hasData('flag_code')) {
            $this->_flagCode = $this->getData('flag_code');
        }
        $this->_init('Magento\Framework\Flag\Resource');
    }

    /**
     * Processing object before save data
     *
     * @throws \Magento\Framework\Model\Exception
     * @return $this
     */
    public function beforeSave()
    {
        if (is_null($this->_flagCode)) {
            throw new \Magento\Framework\Model\Exception(__('Please define flag code.'));
        }

        $this->setFlagCode($this->_flagCode);
        if (!$this->hasKeepUpdateDate()) {
            $this->setLastUpdate(date('Y-m-d H:i:s'));
        }

        return parent::beforeSave();
    }

    /**
     * Retrieve flag data
     *
     * @return mixed
     */
    public function getFlagData()
    {
        if ($this->hasFlagData()) {
            return unserialize($this->getData('flag_data'));
        } else {
            return null;
        }
    }

    /**
     * Set flag data
     *
     * @param mixed $value
     * @return $this
     */
    public function setFlagData($value)
    {
        return $this->setData('flag_data', serialize($value));
    }

    /**
     * load self (load by flag code)
     *
     * @throws \Magento\Framework\Model\Exception
     * @return $this
     */
    public function loadSelf()
    {
        if (is_null($this->_flagCode)) {
            throw new \Magento\Framework\Model\Exception(__('Please define flag code.'));
        }

        return $this->load($this->_flagCode, 'flag_code');
    }
}
