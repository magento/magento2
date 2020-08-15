<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Catalog\Api\Data\CategoryLinkExtensionInterface;
use Magento\Catalog\Api\Data\CategoryLinkInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * @codeCoverageIgnore
 */
class CategoryLink extends AbstractExtensibleModel implements CategoryLinkInterface
{
    public const KEY_POSITION = 'position';
    public const KEY_CATEGORY_ID = 'category_id';

    /**
     * @inheritdoc
     */
    public function getPosition()
    {
        return $this->getData(self::KEY_POSITION);
    }

    /**
     * @inheritdoc
     */
    public function getCategoryId()
    {
        return $this->getData(self::KEY_CATEGORY_ID);
    }

    /**
     * @inheritDoc
     *
     * @param int $position
     * @return $this
     */
    public function setPosition($position)
    {
        return $this->setData(self::KEY_POSITION, $position);
    }

    /**
     * Set category id
     *
     * @param string $categoryId
     * @return $this
     */
    public function setCategoryId($categoryId)
    {
        return $this->setData(self::KEY_CATEGORY_ID, $categoryId);
    }

    /**
     * @inheritdoc
     *
     * @return \Magento\Catalog\Api\Data\CategoryLinkExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     *
     * @param \Magento\Catalog\Api\Data\CategoryLinkExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(CategoryLinkExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
