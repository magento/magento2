<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model\Product\Media;

/**
 * @codeCoverageIgnore
 */
class GalleryEntryContent extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryContentInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEntryData()
    {
        return $this->getData(self::DATA);
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType()
    {
        return $this->getData(self::MIME_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }
}
