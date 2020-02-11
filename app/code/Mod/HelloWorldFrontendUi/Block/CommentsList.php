<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorldFrontendUi\Block;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mod\HelloWorld\Model\ApprovedExtraCommentsLoader;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * HelloWorldFrontendUi product extra comments list block.
 */
class CommentsList extends Template
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var ApprovedExtraCommentsLoader
     */
    private $commentLoader;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ApprovedExtraCommentsLoader $commentLoader
     * @param CustomerRepositoryInterface $customerRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ApprovedExtraCommentsLoader $commentLoader,
        CustomerRepositoryInterface $customerRepository,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->commentLoader = $commentLoader;
        $this->customerRepository = $customerRepository;
        parent::__construct($context, $data);
    }

    /**
     * Gets approved comments list array.
     *
     * @return array
     */
    public function getCommentList(): array
    {
        $productObject = $this->coreRegistry->registry('current_product');
        $productSku = $productObject->getSku();

        return $this->commentLoader->execute($productSku);
    }

    /**
     * Returns string if empty comment list.
     *
     * @return string
     */
    public function checkIsEmpty(): string
    {
        $productObject = $this->coreRegistry->registry('current_product');
        $productSku = $productObject->getSku();
        $commentList = $this->commentLoader->execute($productSku);
        if (count($commentList) == 0) {
            return 'There is no comments yet...';
        }
        return '';
    }

    /**
     * Gets customer name.
     *
     * @param int $id
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCustomerName($id): string
    {
        $customer = $this->customerRepository->getById($id);

        return $customer->getFirstname();
    }
}
