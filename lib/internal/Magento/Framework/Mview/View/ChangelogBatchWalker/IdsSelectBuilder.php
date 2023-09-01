<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mview\View\ChangelogBatchWalker;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Mview\View\ChangelogInterface;

class IdsSelectBuilder implements IdsSelectBuilderInterface
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function build(ChangelogInterface $changelog): Select
    {
        $changelogTableName = $this->resourceConnection->getTableName($changelog->getName());

        $connection = $this->resourceConnection->getConnection();

        return $connection->select()
            ->from($changelogTableName, [$changelog->getColumnName()]);
    }
}
