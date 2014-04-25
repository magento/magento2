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
    protected function _beforeSave()
    {
        if (is_null($this->_flagCode)) {
            throw new \Magento\Framework\Model\Exception(__('Please define flag code.'));
        }

        $this->setFlagCode($this->_flagCode);
        $this->setLastUpdate(date('Y-m-d H:i:s'));

        return parent::_beforeSave();
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
