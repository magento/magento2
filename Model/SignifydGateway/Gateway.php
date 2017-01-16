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
     * Returns id of created case (investigation) on Signifyd service
     * @see https://www.signifyd.com/docs/api/#/reference/cases/create-a-case
     *
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
     * Returns guaranty decision result
     * @see https://www.signifyd.com/docs/api/#/reference/guarantees/submit-a-case-for-guarantee
     *
     * @param int $signifydCaseId
     * @return string
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

        $disposition = $this->processDispositionResult($guaranteeCreationResult);
        return $disposition;
    }

    /**
     * Sends request to cancel guarantee and returns disposition.
     *
     * @see https://www.signifyd.com/docs/api/#/reference/guarantees/submit-a-case-for-guarantee/cancel-guarantee
     * @param int $caseId
     * @return string
     * @throws GatewayException
     */
    public function cancelGuarantee($caseId)
    {
        $result = $this->apiClient->makeApiCall(
            '/cases/' . $caseId . '/guarantee',
            'PUT',
            [
                'guaranteeDisposition' => self::GUARANTEE_CANCELED
            ]
        );

        $disposition = $this->processDispositionResult($result);
        return $disposition;
    }

    /**
     * Processes result from Signifyd API.
     * Throws the GatewayException is result does not contain guarantee disposition in response or
     * disposition has unknown status.
     *
     * @param array $result
     * @return string
     * @throws GatewayException
     */
    private function processDispositionResult(array $result)
    {
        if (!isset($result['disposition'])) {
            throw new GatewayException('Expected field "disposition" missed.');
        }

        $disposition = strtoupper($result['disposition']);

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
