<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\MessageGenerators;

use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Model\MessageGeneratorException;
use Magento\Signifyd\Model\MessageGeneratorInterface;
use Magento\Signifyd\Model\Validators\CaseIdValidator;

/**
 * Generates message based on previous and current Case scores.
 */
class CaseRescore implements MessageGeneratorInterface
{
    /**
     * @var CaseRepositoryInterface
     */
    private $caseRepository;

    /**
     * @var CaseIdValidator
     */
    private $caseIdValidator;

    /**
     * CaseRescore constructor.
     *
     * @param CaseRepositoryInterface $caseRepository
     * @param CaseIdValidator $caseIdValidator
     */
    public function __construct(CaseRepositoryInterface $caseRepository, CaseIdValidator $caseIdValidator)
    {
        $this->caseRepository = $caseRepository;
        $this->caseIdValidator = $caseIdValidator;
    }

    /**
     * @inheritdoc
     */
    public function generate(array $data)
    {
        if (!$this->caseIdValidator->validate($data)) {
            throw new MessageGeneratorException(__('The "%1" should not be empty.', 'caseId'));
        }

        $caseEntity = $this->caseRepository->getByCaseId($data['caseId']);

        if ($caseEntity === null) {
            throw new MessageGeneratorException(__('Case entity not found.'));
        }

        return __(
            'Case Update: New score for the order is %1. Previous score was %2.',
            !empty($data['score']) ? $data['score'] : 0,
            $caseEntity->getScore()
        );
    }
}
