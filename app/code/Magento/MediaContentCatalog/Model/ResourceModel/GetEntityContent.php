<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentCatalog\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\MediaContentApi\Model\GetEntityContentsInterface;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;
use Magento\Eav\Model\Config;

/**
 * Get concatenated content for all store views
 */
class GetEntityContent implements GetEntityContentsInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param Config $config
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        Config $config,
        ResourceConnection $resourceConnection
    ) {
        $this->config = $config;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get product content for all store views
     *
     * @param ContentIdentityInterface $contentIdentity
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(ContentIdentityInterface $contentIdentity): array
    {
        $attribute = $this->config->getAttribute($contentIdentity->getEntityType(), $contentIdentity->getField());
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()->from(
            ['abt' => $attribute->getBackendTable()],
            'abt.value'
        )->where(
            $connection->quoteIdentifier('abt.attribute_id') . ' = ?',
            (int) $attribute->getAttributeId()
        )->where(
            $connection->quoteIdentifier('abt.' . $attribute->getEntityIdField()) . ' = ?',
            $contentIdentity->getEntityId()
        )->distinct(true);

        return $connection->fetchCol($select);
    }
}
