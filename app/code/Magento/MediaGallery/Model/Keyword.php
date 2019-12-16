<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGallery\Model;

use Magento\MediaGalleryApi\Api\Data\KeywordExtensionInterface;
use Magento\MediaGalleryApi\Api\Data\KeywordInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Asset's Keyword
 */
class Keyword extends AbstractExtensibleModel implements KeywordInterface
{
    private const ID = 'id';

    private const KEYWORD = 'keyword';

    /**
     * @inheritdoc
     */
    public function getId(): ?int
    {
        $id = $this->getData(self::ID);

        if (!$id) {
            return null;
        }

        return (int) $id;
    }

    /**
     * @inheritdoc
     */
    public function getKeyword(): string
    {
        return (string)$this->getData(self::KEYWORD);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): KeywordExtensionInterface
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(KeywordExtensionInterface $extensionAttributes): void
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
