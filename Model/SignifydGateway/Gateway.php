<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway;

use Magento\Signifyd\Model\SignifydGateway\Request\CreateCaseBuilderInterface;
use Magento\Signifyd\Model\SignifydGateway\ApiClient;

/**
 * Signifyd Gateway.
 *
 * Encapsulates interaction with Signifyd API.
 */
class Gateway
{
    /**
     * @var CreateCaseBuilderInterface
     */
    private $createCaseBuilder;

    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * Gateway constructor.
     *
     * @param CreateCaseBuilderInterface $createCaseBuilder
     * @param ApiClient $apiClient
     */
    public function __construct(
        CreateCaseBuilderInterface $createCaseBuilder,
        ApiClient $apiClient
    ) {
        $this->createCaseBuilder = $createCaseBuilder;
        $this->apiClient = $apiClient;
    }

    /**
     * @param int $orderId
     * @return int Signifyd case (investigation) identifier
     * @throws GatewayException
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
            throw new GatewayException('Expected field "investigationId" missed.');
        }

        return (int)$caseCreationResult['investigationId'];
    }

    /**
     * @param int $signifydCaseId
     * @return array
     * @throws GatewayException
     */
    public function submitCaseForGuarantee($signifydCaseId)
    {
        $guaranteeCreationResult = $this->apiClient->makeApiCall(
            '/guarantees',
            'POST',
            [
                'caseId' => $signifydCaseId,
            ]
        );

        return $guaranteeCreationResult;
    }
}
