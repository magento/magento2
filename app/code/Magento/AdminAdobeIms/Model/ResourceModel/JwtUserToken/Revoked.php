<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model\ResourceModel\JwtUserToken;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class Revoked
{
    private const TABLE = 'jwt_auth_revoked';

    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->connection = $resourceConnection;
    }

    /**
     * Deleting all the records from the table
     */
    public function deleteAllRecords()
    {
        $connection = $this->getAdapter();
        $connection->delete(
            self::TABLE,
            ['1 = 1']
        );

    }

    /**
     * Resource connection
     *
     * @return AdapterInterface
     */
    private function getAdapter(): AdapterInterface
    {
        return $this->connection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
    }

}
