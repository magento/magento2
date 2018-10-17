<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Model\SignifydGateway\Request\CreateCaseBuilderInterface;

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
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CaseRepositoryInterface
     */
    private $caseRepository;

    /**
     * Gateway constructor.
     *
     * @param CreateCaseBuilderInterface $createCaseBuilder
     * @param ApiClient $apiClient
     * @param OrderRepositoryInterface $orderRepository
     * @param CaseRepositoryInterface $caseRepository
     */
    public function __construct(
        CreateCaseBuilderInterface $createCaseBuilder,
        ApiClient $apiClient,
        OrderRepositoryInterface $orderRepository,
        CaseRepositoryInterface $caseRepository
    ) {
        $this->createCaseBuilder = $createCaseBuilder;
        $this->apiClient = $apiClient;
        $this->orderRepository = $orderRepository;
        $this->caseRepository = $caseRepository;
    }

    /**
     * Returns id of created case (investigation) on Signifyd service
     * @see https://www.signifyd.com/docs/api/#/reference/cases/create-a-case
     *
     * @param int $orderId
     * @return int Signifyd case (investigation) identifier
     * @throws GatewayException
     * @throws \Zend_Http_Client_Exception
     */
    public function createCase($orderId)
    {
        $caseParams = $this->createCaseBuilder->build($orderId);
        $storeId = $this->getStoreIdFromOrder($orderId);

        $caseCreationResult = $this->apiClient->makeApiCall(
            '/cases',
            'POST',
            $caseParams,
            $storeId
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
     * @throws \Zend_Http_Client_Exception
     */
    public function submitCaseForGuarantee($signifydCaseId)
    {
        $storeId = $this->getStoreIdFromCase($signifydCaseId);
        $guaranteeCreationResult = $this->apiClient->makeApiCall(
            '/guarantees',
            'POST',
            [
                'caseId' => $signifydCaseId,
            ],
            $storeId
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
     * @throws \Zend_Http_Client_Exception
     */
    public function cancelGuarantee($caseId)
    {
        $storeId = $this->getStoreIdFromCase($caseId);
        $result = $this->apiClient->makeApiCall(
            '/cases/' . $caseId . '/guarantee',
            'PUT',
            [
                'guaranteeDisposition' => self::GUARANTEE_CANCELED
            ],
            $storeId
        );

        $disposition = $this->processDispositionResult($result);
        if ($disposition !== self::GUARANTEE_CANCELED) {
            throw new GatewayException("API returned unexpected disposition: $disposition.");
        }

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

    /**
     * Returns store id by case.
     *
     * @param int $caseId
     * @return int|null
     */
    private function getStoreIdFromCase(int $caseId)
    {
        $case = $this->caseRepository->getByCaseId($caseId);
        $orderId = $case->getOrderId();

        return $this->getStoreIdFromOrder($orderId);
    }

    /**
     * Returns store id from order.
     *
     * @param int $orderId
     * @return int|null
     */
    private function getStoreIdFromOrder(int $orderId)
    {
        $order = $this->orderRepository->get($orderId);

        return $order->getStoreId();
    }
}
