<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Data\Design\Config;

use Magento\Framework\Api\AbstractExtensibleObject;
use Magento\Theme\Api\Data\DesignConfigDataInterface;
use Magento\Theme\Api\Data\DesignConfigDataExtensionInterface;

/**
 * Class \Magento\Theme\Model\Data\Design\Config\Data
 *
 * @since 2.1.0
 */
class Data extends AbstractExtensibleObject implements DesignConfigDataInterface
{
    /**
     * @inheritDoc
     * @since 2.1.0
     */
    public function getPath()
    {
        return $this->_get(self::PATH);
    }

    /**
     * @inheritDoc
     * @since 2.1.0
     */
    public function getValue()
    {
        return $this->_get(self::VALUE);
    }

    /**
     * @inheritDoc
     * @since 2.1.0
     */
    public function getFieldConfig()
    {
        return $this->_get(self::FIELD_CONFIG);
    }

    /**
     * @inheritDoc
     * @since 2.1.0
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * @inheritDoc
     * @since 2.1.0
     */
    public function setPath($path)
    {
        return $this->setData(self::PATH, $path);
    }

    /**
     * @inheritDoc
     * @since 2.1.0
     */
    public function setFieldConfig(array $config)
    {
        return $this->setData(self::FIELD_CONFIG, $config);
    }

    /**
     * @inheritDoc
     * @since 2.1.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     * @since 2.1.0
     */
    public function setExtensionAttributes(DesignConfigDataExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
