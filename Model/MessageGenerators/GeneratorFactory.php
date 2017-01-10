<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\MessageGenerators;

use Magento\Framework\ObjectManagerInterface;
use Magento\Signifyd\Model\MessageGeneratorInterface;

/**
 * Creates instance of message generator based on received type of message.
 */
class GeneratorFactory
{
    /**
     * Type of message for Signifyd case creation.
     * @var string
     */
    private static $caseCreation = 'cases/creation';

    /**
     * Type of message for Signifyd case re-scoring.
     * @var string
     */
    private static $caseRescore = 'cases/rescore';

    /**
     * Type of message for Signifyd case reviewing
     * @var string
     */
    private static $caseReview = 'cases/review';

    /**
     * Type of message of Signifyd guarantee completion
     * @var string
     */
    private static $guaranteeCompletion = 'guarantees/completion';

    /**
     * Type of message of Signifyd guarantee creation
     * @var string
     */
    private static $guaranteeCreation = 'guarantees/creation';

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
     * Creates instance of message generator.
     * Throws exception if type of message generator does not have implementations.
     *
     * @param string $type
     * @return MessageGeneratorInterface
     * @throws \InvalidArgumentException
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
            case self::$guaranteeCreation:
                $className = GuaranteeCreation::class;
                break;
            default:
                throw new \InvalidArgumentException('Specified message type does not supported.');
        }

        return $this->objectManager->create($className);
    }
}
