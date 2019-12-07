<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Data\Design\Config;

use Magento\Framework\Api\AbstractExtensibleObject;
use Magento\Theme\Api\Data\DesignConfigDataInterface;
use Magento\Theme\Api\Data\DesignConfigDataExtensionInterface;

class Data extends AbstractExtensibleObject implements DesignConfigDataInterface
{
    /**
     * @inheritDoc
     */
    public function getPath()
    {
        return $this->_get(self::PATH);
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->_get(self::VALUE);
    }

    /**
     * @inheritDoc
     */
    public function getFieldConfig()
    {
        return $this->_get(self::FIELD_CONFIG);
    }

    /**
     * @inheritDoc
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * @inheritDoc
     */
    public function setPath($path)
    {
        return $this->setData(self::PATH, $path);
    }

    /**
     * @inheritDoc
     */
    public function setFieldConfig(array $config)
    {
        return $this->setData(self::FIELD_CONFIG, $config);
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(DesignConfigDataExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
