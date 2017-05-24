<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule4\Service\V1;

use Magento\TestModule4\Service\V1\Entity\DataObjectRequest;
use Magento\TestModule4\Service\V1\Entity\DataObjectResponseFactory;
use Magento\TestModule4\Service\V1\Entity\ExtensibleRequestInterface;
use Magento\TestModule4\Service\V1\Entity\NestedDataObjectRequest;

class DataObjectService implements \Magento\TestModule4\Service\V1\DataObjectServiceInterface
{
    /**
     * @var DataObjectResponseFactory
     */
    protected $responseFactory;

    /**
     * @param DataObjectResponseFactory $responseFactory
     */
    public function __construct(DataObjectResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getData($id)
    {
        return $this->responseFactory->create()->setEntityId($id)->setName("Test");
    }

    /**
     * {@inheritdoc}
     */
    public function updateData($id, DataObjectRequest $request)
    {
        return $this->responseFactory->create()->setEntityId($id)->setName($request->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function nestedData($id, NestedDataObjectRequest $request)
    {
        return $this->responseFactory->create()->setEntityId($id)->setName($request->getDetails()->getName());
    }

    /**
     * Test return scalar value
     *
     * @param int $id
     * @return int
     */
    public function scalarResponse($id)
    {
        return $id;
    }

    /**
     * {@inheritdoc}
     */
    public function extensibleDataObject($id, ExtensibleRequestInterface $request)
    {
        return $this->responseFactory->create()->setEntityId($id)->setName($request->getName());
    }
}
