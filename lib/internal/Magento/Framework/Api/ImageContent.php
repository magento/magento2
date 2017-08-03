<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

use Magento\Framework\Api\Data\ImageContentInterface;

/**
 * Image Content data object
 *
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class ImageContent extends AbstractSimpleObject implements ImageContentInterface
{
    /**
     * {@inheritdoc}
     *
     * @return string
     * @since 2.0.0
     */
    public function getBase64EncodedData()
    {
        return $this->_get(self::BASE64_ENCODED_DATA);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     * @since 2.0.0
     */
    public function getType()
    {
        return $this->_get(self::TYPE);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     * @since 2.0.0
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $data
     * @return $this
     * @since 2.0.0
     */
    public function setBase64EncodedData($data)
    {
        return $this->setData(self::BASE64_ENCODED_DATA, $data);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $mimeType
     * @return $this
     * @since 2.0.0
     */
    public function setType($mimeType)
    {
        return $this->setData(self::TYPE, $mimeType);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }
}
