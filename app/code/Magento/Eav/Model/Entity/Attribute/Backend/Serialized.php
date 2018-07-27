<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute\Backend;

use Magento\Framework\Unserialize\SecureUnserializer;
use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;

/**
 * "Serialized" attribute backend
 */
class Serialized extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var SecureUnserializer
     */
    private $unserializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param SecureUnserializer|null $unserializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        SecureUnserializer $unserializer = null,
        LoggerInterface $logger = null
    ) {
        $this->unserializer = $unserializer ?: ObjectManager::getInstance()->get(SecureUnserializer::class);
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }
    
    /**
     * Serialize before saving
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function beforeSave($object)
    {
        // parent::beforeSave() is not called intentionally
        $attrCode = $this->getAttribute()->getAttributeCode();
        if ($object->hasData($attrCode)) {
            $object->setData($attrCode, serialize($object->getData($attrCode)));
        }

        return $this;
    }

    /**
     * Unserialize after saving
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function afterSave($object)
    {
        parent::afterSave($object);
        $this->_unserialize($object);
        return $this;
    }

    /**
     * Unserialize after loading
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function afterLoad($object)
    {
        parent::afterLoad($object);
        $this->_unserialize($object);
        return $this;
    }

    /**
     * Try to unserialize the attribute value
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    protected function _unserialize(\Magento\Framework\DataObject $object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        if ($object->getData($attrCode)) {
            try {
                $unserialized = $this->unserializer->unserialize($object->getData($attrCode));
                $object->setData($attrCode, $unserialized);
            } catch (\InvalidArgumentException $e) {
                $this->logger->critical($e);
                $object->unsetData($attrCode);
            } catch (\Exception $e){
                $object->unsetData($attrCode);
            }
        }

        return $this;
    }
}
