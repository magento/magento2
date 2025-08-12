<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompareListGraphQl\Model\Resolver;

use Magento\Catalog\Model\CompareListFactory;
use Magento\Catalog\Model\MaskedListIdToCompareListId;
use Magento\Catalog\Model\ResourceModel\Product\Compare\CompareList as CompareListResource;
use Magento\CompareListGraphQl\Model\Service\Customer\GetListIdByCustomerId;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class for deleting compare list
 */
class DeleteCompareList implements ResolverInterface
{
    /**
     * @var CompareListFactory
     */
    private $compareListFactory;

    /**
     * @var CompareListResource
     */
    private $compareListResource;

    /**
     * @var MaskedListIdToCompareListId
     */
    private $maskedListIdToCompareListId;

    /**
     * @var GetListIdByCustomerId
     */
    private $getListIdByCustomerId;

    /**
     * @param CompareListFactory $compareListFactory
     * @param CompareListResource $compareListResource
     * @param MaskedListIdToCompareListId $maskedListIdToCompareListId
     * @param GetListIdByCustomerId $getListIdByCustomerId
     */
    public function __construct(
        CompareListFactory $compareListFactory,
        CompareListResource $compareListResource,
        MaskedListIdToCompareListId $maskedListIdToCompareListId,
        GetListIdByCustomerId $getListIdByCustomerId
    ) {
        $this->compareListFactory = $compareListFactory;
        $this->compareListResource = $compareListResource;
        $this->maskedListIdToCompareListId = $maskedListIdToCompareListId;
        $this->getListIdByCustomerId = $getListIdByCustomerId;
    }

    /**
     * Remove compare list
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return Value|mixed|void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (empty($args['uid'])) {
            throw new GraphQlInputException(__('"uid" value must be specified'));
        }

        try {
            $listId = $this->maskedListIdToCompareListId->execute($args['uid'], $context->getUserId());
        } catch (LocalizedException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }
        $removed = ['result' => false];

        if ($userId = $context->getUserId()) {
            $customerListId = $this->getListIdByCustomerId->execute($userId);
            if ($listId === $customerListId) {
                try {
                    $removed['result'] = $this->deleteCompareList($customerListId);
                } catch (LocalizedException $exception) {
                    throw new GraphQlInputException(
                        __('Something was wrong during removing compare list')
                    );
                }
            }
        }

        if ($listId) {
            try {
                $removed['result'] = $this->deleteCompareList($listId);
            } catch (LocalizedException $exception) {
                throw new GraphQlInputException(
                    __('Something was wrong during removing compare list')
                );
            }
        }

        return $removed;
    }

    /**
     * Delete compare list
     *
     * @param int|null $listId
     * @return bool
     */
    private function deleteCompareList(?int $listId): bool
    {
        $compareList = $this->compareListFactory->create();
        $compareList->setListId($listId);
        $this->compareListResource->delete($compareList);

        return true;
    }
}
