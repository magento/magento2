<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Select;
use Magento\Framework\App\ResourceConnection;

/**
<<<<<<< HEAD
 * Class for fast retrieval of all product images
=======
 * Class for retrieval of all product images
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
 */
class Image
{
    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var Generator
     */
    private $batchQueryGenerator;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param Generator $generator
     * @param ResourceConnection $resourceConnection
     * @param int $batchSize
     */
    public function __construct(
        Generator $generator,
        ResourceConnection $resourceConnection,
        $batchSize = 100
    ) {
        $this->batchQueryGenerator = $generator;
        $this->resourceConnection = $resourceConnection;
        $this->connection = $this->resourceConnection->getConnection();
        $this->batchSize = $batchSize;
    }

    /**
     * Returns product images
     *
     * @return \Generator
     */
    public function getAllProductImages(): \Generator
    {
        $batchSelectIterator = $this->batchQueryGenerator->generate(
            'value_id',
            $this->getVisibleImagesSelect(),
            $this->batchSize,
            \Magento\Framework\DB\Query\BatchIteratorInterface::NON_UNIQUE_FIELD_ITERATOR
        );

        foreach ($batchSelectIterator as $select) {
            foreach ($this->connection->fetchAll($select) as $key => $value) {
                yield $key => $value;
            }
        }
    }

    /**
     * Get the number of unique pictures of products
<<<<<<< HEAD
=======
     *
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @return int
     */
    public function getCountAllProductImages(): int
    {
<<<<<<< HEAD
        $select = $this->getVisibleImagesSelect()->reset('columns')->columns('count(*)');
=======
        $select = $this->getVisibleImagesSelect()
            ->reset('columns')
            ->reset('distinct')
            ->columns(
                new \Zend_Db_Expr('count(distinct value)')
            );

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return (int) $this->connection->fetchOne($select);
    }

    /**
<<<<<<< HEAD
=======
     * Return Select to fetch all products images
     *
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @return Select
     */
    private function getVisibleImagesSelect(): Select
    {
        return $this->connection->select()->distinct()
            ->from(
                ['images' => $this->resourceConnection->getTableName(Gallery::GALLERY_TABLE)],
                'value as filepath'
            )->where(
                'disabled = 0'
            );
    }
}
