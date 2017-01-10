<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
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
    /**#@+
     * Constants for case available statuses
     */
    const STATUS_OPEN = 'OPEN';
    const STATUS_PROCESSING = 'PROCESSING';
    const STATUS_FLAGGED = 'FLAGGED';
    const STATUS_DISMISSED = 'DISMISSED';
    /**#@-*/

    /**#@+
     * Constants for guarantee available statuses
     * @see https://www.signifyd.com/resources/manual/signifyd-guarantee/signifyd-guarantee/
     */
    const GUARANTEE_APPROVED = 'APPROVED';
    const GUARANTEE_DECLINED = 'DECLINED';
    const GUARANTEE_PENDING = 'PENDING';
    const GUARANTEE_CANCELED = 'CANCELED';
    const GUARANTEE_IN_REVIEW = 'IN_REVIEW';
    const GUARANTEE_UNREQUESTED = 'UNREQUESTED';
    /**#@-*/

    /**#@+
     * Constants for case available review dispositions
     */
    const DISPOSITION_GOOD = 'GOOD';
    const DISPOSITION_FRAUDULENT = 'FRAUDULENT';
    const DISPOSITION_UNSET = 'UNSET';

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

        if (!isset($guaranteeCreationResult['disposition'])) {
            throw new GatewayException('Expected field "disposition" missed.');
        }

        $disposition = strtoupper($guaranteeCreationResult['disposition']);

        if (!in_array($disposition, [
            self::GUARANTEE_APPROVED,
            self::GUARANTEE_DECLINED,
            self::GUARANTEE_PENDING,
            self::GUARANTEE_CANCELED,
            self::GUARANTEE_IN_REVIEW,
            self::GUARANTEE_UNREQUESTED
        ])) {
            throw new GatewayException(
                sprintf('API returns unknown guaranty disposition "%s".', $disposition)
            );
        }

        return $disposition;
    }
}
