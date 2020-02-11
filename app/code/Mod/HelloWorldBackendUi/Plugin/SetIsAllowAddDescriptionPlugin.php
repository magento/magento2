<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorldBackendUi\Plugin;

use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\App\Request\Http;
use Mod\HelloWorldApi\Api\ExtraAbilitiesProviderInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\App\ResourceConnection;

/**
 * Add is allow add description plugin class.
 */
class SetIsAllowAddDescriptionPlugin
{
    /**
     * @var Http
     */
    private $request;

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
    const QUOTE_TABLE = 'customer_extra_abilities';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * Plugin constructor.
     *
     * @param Http $request
     * @param ExtraAbilitiesProviderInterface $extraAbilitiesProvider
     * @param ExtensibleDataObjectConverter $dataObjectConverter
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        Http $request,
        ExtraAbilitiesProviderInterface $extraAbilitiesProvider,
        ExtensibleDataObjectConverter $dataObjectConverter,
        ResourceConnection $resourceConnection
    ) {
        $this->request = $request;
        $this->extraAbilitiesProvider = $extraAbilitiesProvider;
        $this->dataObjectConverter = $dataObjectConverter;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Saving extra abilities.
     *
     * @param CustomerRepository $subject
     * @param array $data
     * @return mixed
     */
    public function afterSave(CustomerRepository $subject, $data)
    {
        $connection = $this->resourceConnection->getConnection();
        $customerId = (int)$this->request->getParam('customer_id');
        if ($customerId == null) {
            $sql = /** @lang text */
                'SELECT MAX(entity_id) FROM customer_entity';
            $maxCustomerId = $connection->fetchRow($sql);
            $customerId = (int)$maxCustomerId['MAX(entity_id)'];
        }
        $customerExtraAttributesObject = $this->extraAbilitiesProvider->getExtraAbilities($customerId);
        if (!empty($customerExtraAttributesObject)) {
            $customerExtraAttributes = $this->dataObjectConverter->toFlatArray($customerExtraAttributesObject[0], []);
            $customer = $this->request->getParams();
            $customerAllowAddDesc = 0;
            if (isset($customer['customer'])) {
                if ($customer['customer']['is_allowed_add_description'] !== null) {
                    $customerAllowAddDesc = $customer['customer']['is_allowed_add_description'];
                }
            }
            if ($customerExtraAttributes['is_allowed_add_description'] !== $customerAllowAddDesc) {
                if ($customer) {
                    $updateData = ["is_allowed_add_description" => $customer['customer']['is_allowed_add_description']];
                } else {
                    $updateData = [
                        "is_allowed_add_description" =>
                            $customerExtraAttributes['is_allowed_add_description'],
                    ];
                }
                $where = ['customer_id = ?' => $customerId];
                $tableName = $connection->getTableName(self::QUOTE_TABLE);
                $connection->update($tableName, $updateData, $where);

                return $data;
            }
        } else {
            $insertData = [
                "customer_id" => $customerId,
                "is_allowed_add_description" => 0,
            ];
            $tableName = $connection->getTableName(self::QUOTE_TABLE);
            $connection->insert($tableName, $insertData);

            return $data;
        }

        return $data;
    }
}
