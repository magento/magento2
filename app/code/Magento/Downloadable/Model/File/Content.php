<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\File;

use Magento\Downloadable\Api\Data\File\ContentInterface;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class Content extends \Magento\Framework\Model\AbstractExtensibleModel implements ContentInterface
{
    const DATA = 'file_data';
    const NAME = 'name';

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getFileData()
    {
        return $this->getData(self::DATA);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Set data (base64 encoded content)
     *
     * @param string $fileData
     * @return $this
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function setFileData($fileData)
    {
        return $this->setData(self::DATA, $fileData);
    }

    /**
     * Set file name
     *
     * @param string $name
     * @return $this
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Downloadable\Api\Data\File\ContentExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Downloadable\Api\Data\File\ContentExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Downloadable\Api\Data\File\ContentExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
