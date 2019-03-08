<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Model\ResourceModel;

use Magento\MessageQueue\Api\Data\PoisonPillInterface;
use Magento\MessageQueue\Api\Data\PoisonPillInterfaceFactory;
use Magento\MessageQueue\Api\PoisonPillReadInterface;
use Magento\MessageQueue\Api\PoisonPillPutInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PoisonPill extends AbstractDb implements PoisonPillPutInterface, PoisonPillReadInterface
{
    /**
     * Table name.
     */
    const QUEUE_POISON_PILL_TABLE = 'queue_poison_pill';

    /**
     * @var PoisonPillInterfaceFactory
     */
    private $poisonPillFactory;

    /**
     * PoisonPill constructor.
     *
     * @param Context $context
     * @param PoisonPillInterfaceFactory $poisonPillFactory
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        PoisonPillInterfaceFactory $poisonPillFactory,
        string $connectionName = null
    ) {
        $this->poisonPillFactory = $poisonPillFactory;
        parent::__construct($context, $connectionName);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(self::QUEUE_POISON_PILL_TABLE, 'version');
    }

    /**
     * @inheritdoc
     */
    public function put(): int
    {
        /** @var PoisonPillInterface $poisonPill */
        $poisonPill = $this->poisonPillFactory->create();
        return $this->save($poisonPill)->getConnection()->lastInsertId();
    }

    /**
     * @inheritdoc
     */
    public function getLatest() : PoisonPillInterface
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable(self::QUEUE_POISON_PILL_TABLE),
            'version'
        )->order(
            'version ' . \Magento\Framework\DB\Select::SQL_DESC
        )->limit(
            1
        );

        $version = $this->getConnection()->fetchOne($select);

        $poisonPill = $this->poisonPillFactory->create(['data' => ['version' => (int) $version]]);

        return $poisonPill;
    }
}
