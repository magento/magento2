<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorld\Model;

use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Exception\CouldNotSaveException;
use Mod\HelloWorldApi\Api\ExtraAbilitiesProviderInterface;
use Mod\HelloWorldApi\Api\ExtraCommentSaverInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ResourceConnection;

/**
 * Extra Comment saver service class.
 * @api
 */
class ExtraCommentSaver implements ExtraCommentSaverInterface
{
    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var ExtraAbilitiesProviderInterface
     */
    private $extraAbilitiesProvider;

    /**
     * @var ExtensibleDataObjectConverter
     */
    private $dataObjectConverter;

    /**
     * @var string
     */
    const QUOTE_TABLE = 'product_extra_comments';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ProductFactory $productFactory
     * @param ExtraAbilitiesProviderInterface $extraAbilitiesProvider
     * @param ExtensibleDataObjectConverter $dataObjectConverter
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ProductFactory $productFactory,
        ExtraAbilitiesProviderInterface $extraAbilitiesProvider,
        ExtensibleDataObjectConverter $dataObjectConverter,
        ResourceConnection $resourceConnection
    ) {
        $this->productFactory = $productFactory;
        $this->extraAbilitiesProvider = $extraAbilitiesProvider;
        $this->dataObjectConverter = $dataObjectConverter;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function execute(int $customerId, string $productSku, string $extraComment): bool
    {
        try {
            $product = $this->productFactory->create();
            $productId = $product->getIdBySku($productSku);
            if (!$productId) {
                throw new CouldNotSaveException(__('Product with SKU ' . '"' . $productSku . '"' . ' not exist!'));
            }
            $customerExtraAttributesObject = $this->extraAbilitiesProvider->getExtraAbilities((int)$customerId);
            if (!empty($customerExtraAttributesObject)) {
                $customerExtraAttributes = $this->dataObjectConverter
                    ->toFlatArray($customerExtraAttributesObject[0], []);
                if ($customerExtraAttributes['is_allowed_add_description'] == 1) {
                    $connection = $this->resourceConnection->getConnection();
                    $data = [
                        "customer_id" => $customerId,
                        "product_sku" => $productSku,
                        "extra_comment" => $extraComment,
                        "is_approved" => 0,
                    ];
                    $tableName = $connection->getTableName(self::QUOTE_TABLE);
                    $connection->insert($tableName, $data);
                } else {
                    throw new CouldNotSaveException(__('You have not permissions to add extra comment!'));
                }
            }
        } catch (CouldNotSaveException $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return true;
    }
}
