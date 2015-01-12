<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule4\Service\V1;

use Magento\TestModule4\Service\V1\Entity\DataObjectRequest;
use Magento\TestModule4\Service\V1\Entity\DataObjectResponseBuilder;
use Magento\TestModule4\Service\V1\Entity\ExtensibleRequestInterface;
use Magento\TestModule4\Service\V1\Entity\NestedDataObjectRequest;

class DataObjectService implements \Magento\TestModule4\Service\V1\DataObjectServiceInterface
{
    /**
     * @var DataObjectResponseBuilder
     */
    protected $responseBuilder;

    /**
     * @param DataObjectResponseBuilder $responseBuilder
     */
    public function __construct(DataObjectResponseBuilder $responseBuilder)
    {
        $this->responseBuilder = $responseBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getData($id)
    {
        return $this->responseBuilder->setEntityId($id)->setName("Test")->create();
    }

    /**
     * {@inheritdoc}
     */
    public function updateData($id, DataObjectRequest $request)
    {
        return $this->responseBuilder->setEntityId($id)->setName($request->getName())->create();
    }

    /**
     * {@inheritdoc}
     */
    public function nestedData($id, NestedDataObjectRequest $request)
    {
        return $this->responseBuilder->setEntityId($id)->setName($request->getDetails()->getName())->create();
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
        return $this->responseBuilder->setEntityId($id)->setName($request->getName())->create();
    }
}
