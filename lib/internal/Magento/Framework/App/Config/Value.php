<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

/**
 * Config data model
 *
 * @method \Magento\Framework\Model\Resource\Db\AbstractDb getResource()
 * @method string getScope()
 * @method \Magento\Framework\App\Config\ValueInterface setScope(string $value)
 * @method int getScopeId()
 * @method \Magento\Framework\App\Config\ValueInterface setScopeId(int $value)
 * @method string getPath()
 * @method \Magento\Framework\App\Config\ValueInterface setPath(string $value)
 * @method \Magento\Framework\App\Config\ValueInterface setValue(string $value)
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class Value extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\App\Config\ValueInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'config_data';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'config_data';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_config;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->_config = $config;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Add availability call after load as public
     *
     * @return void
     */
    public function afterLoad()
    {
        $this->_afterLoad();
    }

    /**
     * Check if config data value was changed
     *
     * @return bool
     */
    public function isValueChanged()
    {
        return $this->getValue() != $this->getOldValue();
    }

    /**
     * Get old value from existing config
     *
     * @return string
     */
    public function getOldValue()
    {
        return (string)$this->_config->getValue(
            $this->getPath(),
            $this->getScope() ?: \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT,
            $this->getScopeCode()
        );
    }

    /**
     * Get value by key for new user data from <section>/groups/<group>/fields/<field>
     *
     * @param string $key
     * @return string
     */
    public function getFieldsetDataValue($key)
    {
        $data = $this->_getData('fieldset_data');
        return is_array($data) && isset($data[$key]) ? $data[$key] : null;
    }
}
