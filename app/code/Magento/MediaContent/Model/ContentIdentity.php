<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;

/**
 * @inheritdoc
 */
class ContentIdentity extends AbstractExtensibleModel implements ContentIdentityInterface
{
    private const TYPE = 'entity_type';
    private const ENTITY_ID = 'entity_id';
    private const FIELD = 'field';

    /**
     * @inheritdoc
     */
    public function getEntityType(): string
    {
        return (string) $this->getData(self::TYPE);
    }

    /**
     * @inheritdoc
     */
    public function getEntityId(): string
    {
        return (string) $this->getData(self::ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function getField(): string
    {
        return (string) $this->getData(self::FIELD);
    }
}
