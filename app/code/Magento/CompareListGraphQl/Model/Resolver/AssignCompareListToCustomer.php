<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Resolver;

use Magento\Catalog\Model\CompareList as ModelCompareList;
use Magento\Catalog\Model\CompareListFactory;
use Magento\Catalog\Model\ResourceModel\CompareList as ResourceCompareList;
use Magento\CompareListGraphQl\Model\Service\CustomerService;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class Assign Customer to CompareList
 */
class AssignCompareListToCustomer implements ResolverInterface
{
    /**
     * @var CompareListFactory
     */
    private $compareListFactory;

    /**
     * @var ResourceCompareList
     */
    private $resourceCompareList;

    /**
     * @var CustomerService
     */
    private $customerService;

    /**
     * @param CompareListFactory $compareListFactory
     * @param ResourceCompareList $resourceCompareList
     * @param CustomerService $customerService
     */
    public function __construct(
        CompareListFactory $compareListFactory,
        ResourceCompareList $resourceCompareList,
        CustomerService $customerService
    ) {
        $this->compareListFactory = $compareListFactory;
        $this->resourceCompareList = $resourceCompareList;
        $this->customerService = $customerService;
    }

    /**
     * Assign compare list to customer
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return Value|mixed|void
     *
     * @throws GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['customerId'])) {
            throw new GraphQlInputException(__('"customerId" value must be specified'));
        }

        if (!isset($args['listId'])) {
            throw new GraphQlInputException(__('"listId" value must be specified'));
        }

        $customer = $this->customerService->validateCustomer($args['customerId']);

        /** @var  $compareListModel ModelCompareList*/
        $compareListModel = $this->compareListFactory->create();
        $this->resourceCompareList->load($compareListModel, $args['listId']);

        if (!$compareListModel->getId()) {
            return false;
        }

        $compareListModel->setCustomerId($customer->getId());
        $this->resourceCompareList->save($compareListModel);

        return true;
    }
}
