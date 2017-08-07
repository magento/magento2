<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

/**
 * Flag model
 *
 * @method \Magento\Framework\Flag\FlagResource _getResource()
 * @method \Magento\Framework\Flag\FlagResource getResource()
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
     * Serializer for encode/decode string/data.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     * @since 2.2.0
     */
    private $json;

    /**
     * Serializer for encode/decode string/data.
     *
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     * @since 2.2.0
     */
    private $serialize;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serialize
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Magento\Framework\Serialize\Serializer\Json $json = null,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize = null
    ) {
        $this->json = $json ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->serialize = $serialize ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Serialize::class);
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

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
        $this->_init(\Magento\Framework\Flag\FlagResource::class);
    }

    /**
     * Processing object before save data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     */
    public function beforeSave()
    {
        if ($this->_flagCode === null) {
            throw new Exception\LocalizedException(new \Magento\Framework\Phrase('Please define flag code.'));
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
            $flagData = $this->getData('flag_data');
            try {
                $data = $this->json->unserialize($flagData);
            } catch (\InvalidArgumentException $exception) {
                $data = $this->serialize->unserialize($flagData);
            }
            return $data;
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
        return $this->setData('flag_data', $this->json->serialize($value));
    }

    /**
     * load self (load by flag code)
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     */
    public function loadSelf()
    {
        if ($this->_flagCode === null) {
            throw new Exception\LocalizedException(new \Magento\Framework\Phrase('Please define flag code.'));
        }

        return $this->load($this->_flagCode, 'flag_code');
    }
}
