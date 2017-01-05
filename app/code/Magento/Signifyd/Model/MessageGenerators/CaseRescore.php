<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\MessageGenerators;

use Magento\Framework\DataObject;
use Magento\Signifyd\Api\CaseRepositoryInterface;
use Magento\Signifyd\Model\MessageGeneratorException;
use Magento\Signifyd\Model\MessageGeneratorInterface;
use Magento\Signifyd\Model\Validators\CaseDataValidator;

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
     * @var CaseDataValidator
     */
    private $caseDataValidator;

    /**
     * CaseRescore constructor.
     *
     * @param CaseRepositoryInterface $caseRepository
     * @param CaseDataValidator $caseDataValidator
     */
    public function __construct(CaseRepositoryInterface $caseRepository, CaseDataValidator $caseDataValidator)
    {
        $this->caseRepository = $caseRepository;
        $this->caseDataValidator = $caseDataValidator;
    }

    /**
     * @inheritdoc
     */
    public function generate(DataObject $data)
    {
        if (!$this->caseDataValidator->validate($data)) {
            throw new MessageGeneratorException(__('The "%1" should not be empty.', 'caseId'));
        }

        $caseEntity = $this->caseRepository->getByCaseId($data->getData('caseId'));

        if ($caseEntity === null) {
            throw new MessageGeneratorException(__('Case entity not found.'));
        }

        return __(
            'Case Update: New score for the order is %1. Previous score was %2.',
            $data->getData('score'),
            $caseEntity->getScore()
        );
    }
}
