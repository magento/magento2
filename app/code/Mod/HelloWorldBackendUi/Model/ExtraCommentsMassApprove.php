<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorldBackendUi\Model;

use Magento\Framework\App\ResourceConnection;

/**
 * Extra Comments mass approve action service class.
 */
class ExtraCommentsMassApprove
{
    /**
     * @var string
     */
    const QUOTE_TABLE = 'product_extra_comments';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var array
     */
    private $comments = [];

    /**
     * @inheritdoc
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Approve comments.
     *
     * @param array $commentIds
     * @return void
     */
    public function execute($commentIds)
    {
        $connection = $this->resourceConnection->getConnection();
        $data = ["is_approved" => 1];
        $tableName = $connection->getTableName(self::QUOTE_TABLE);
        $idsArrayCount = count($commentIds);
        if ($idsArrayCount > 0) {
            foreach ($commentIds as $key => $id) {
                $this->comments[] = $id;
                if ($key == $idsArrayCount - 1) {
                    $where = 'comment_id IN (' .
                        implode(',', $this->comments) . ')';
                    $connection->update($tableName, $data, $where);
                }
                if (count($this->comments) == 25 && $idsArrayCount !== 25) {
                    $where = 'comment_id IN (' .
                        implode(',', $this->comments) . ')';
                    $connection->update($tableName, $data, $where);
                    unset($this->comments);
                    $this->comments = [];
                } elseif (count($this->comments) == 25 && $idsArrayCount == 25) {
                    unset($this->comments);
                    $this->comments = [];
                }
            }
        }
    }
}
