<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Signifyd\Model\MessageGenerators\CaseCreation;
use Magento\Signifyd\Model\MessageGenerators\CaseRescore;
use Magento\Signifyd\Model\MessageGenerators\CaseReview;
use Magento\Signifyd\Model\MessageGenerators\GuaranteeCompletion;

/**
 * Creates instance of case updating service configured with specific message generator.
 * The message generator initialization depends on specified type (like, case creation, re-scoring, review and
 * guarantee completion).
 */
class CaseUpdatingServiceFactory
{
    /**
     * Type of message for Signifyd case creation.
     * @var string
     */
    private static $caseCreation = 'CASE_CREATION';

    /**
     * Type of message for Signifyd case re-scoring.
     * @var string
     */
    private static $caseRescore = 'CASE_RESCORE';

    /**
     * Type of message for Signifyd case reviewing
     * @var string
     */
    private static $caseReview = 'CASE_REVIEW';

    /**
     * Type of message of Signifyd guarantee completion
     * @var string
     */
    private static $guaranteeCompletion = 'GUARANTEE_COMPLETION';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * CaseUpdatingServiceFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Creates instance of service updating case.
     * As param retrieves type of message generator.
     *
     * @param string $type
     * @return CaseUpdatingService
     */
    public function create($type)
    {
        switch ($type) {
            case self::$caseCreation:
                $className = CaseCreation::class;
                break;
            case self::$caseRescore:
                $className = CaseRescore::class;
                break;
            case self::$caseReview:
                $className = CaseReview::class;
                break;
            case self::$guaranteeCompletion:
                $className = GuaranteeCompletion::class;
                break;
            default:
                throw new \InvalidArgumentException('Specified message type does not supported.');
        }

        $messageGenerator = $this->objectManager->get($className);
        $service = $this->objectManager->create(CaseUpdatingService::class, [
            'messageGenerator' => $messageGenerator
        ]);

        return $service;
    }
}
