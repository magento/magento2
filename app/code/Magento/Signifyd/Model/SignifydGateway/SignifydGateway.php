<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway;

use Magento\Signifyd\Model\SignifydGateway\Request\CreateCaseBuilderInterface;
use Magento\Signifyd\Model\SignifydGateway\SignifydApiClient;

/**
 * Signifyd Gateway.
 *
 * Encapsulates interaction with Signifyd API.
 */
class SignifydGateway
{
    /**
     * @var CreateCaseBuilderInterface
     */
    private $createCaseBuilder;

    /**
     * @var SignifydApiClient
     */
    private $apiClient;

    /**
     * SignifydGateway constructor.
     *
     * @param CreateCaseBuilderInterface $createCaseBuilder
     * @param SignifydApiClient $apiClient
     */
    public function __construct(
        CreateCaseBuilderInterface $createCaseBuilder,
        SignifydApiClient $apiClient
    ) {
        $this->createCaseBuilder = $createCaseBuilder;
        $this->apiClient = $apiClient;
    }

    /**
     * @param int $orderId
     * @return int Signifyd case (investigation) identifier
     * @throws SignifydGatewayException
     */
    public function createCase($orderId)
    {
        $caseParams = $this->createCaseBuilder->build($orderId);

        $caseCreationResult = $this->apiClient->makeApiCall(
            '/cases',
            'POST',
            $caseParams
        );

        if (!isset($caseCreationResult['investigationId'])) {
            throw new SignifydGatewayException('Expected field "investigationId" missed.');
        }

        return (int)$caseCreationResult['investigationId'];
    }
}
