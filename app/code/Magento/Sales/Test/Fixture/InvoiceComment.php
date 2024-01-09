<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Sales\Api\Data\CommentInterface;
use Magento\Sales\Api\Data\EntityInterface;
use Magento\Sales\Api\Data\InvoiceCommentInterface;
use Magento\Sales\Api\InvoiceCommentRepositoryInterface;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class InvoiceComment implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        InvoiceCommentInterface::IS_CUSTOMER_NOTIFIED => 0,
        InvoiceCommentInterface::PARENT_ID => 0,
        CommentInterface::COMMENT => 'Test Comment',
        CommentInterface::IS_VISIBLE_ON_FRONT => 0,
        EntityInterface::ENTITY_ID => 0,
        EntityInterface::CREATED_AT => "0000-00-00 00:00:00",
    ];

    /**
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * @var InvoiceCommentRepositoryInterface
     */
    private $invoiceCommentRepository;

    /**
     * @param ServiceFactory $serviceFactory
     * @param InvoiceCommentRepositoryInterface $invoiceCommentRepository
     */
    public function __construct(
        ServiceFactory $serviceFactory,
        InvoiceCommentRepositoryInterface $invoiceCommentRepository
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->invoiceCommentRepository = $invoiceCommentRepository;
    }

    public function apply(array $data = []): ?DataObject
    {
        $service = $this->serviceFactory->create(InvoiceCommentRepositoryInterface::class, 'save');
        $invoiceComment = $service->execute($this->prepareData($data));

        return $this->invoiceCommentRepository->get($invoiceComment->getId());
    }

    public function revert(DataObject $data): void
    {
        $invoice = $this->invoiceCommentRepository->get($data->getId());
        $this->invoiceCommentRepository->delete($invoice);
    }

    /**
     * Prepare invoice data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $data['entity'] = array_merge(self::DEFAULT_DATA, $data);

        return $data;
    }
}
