<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\MessageGenerators;

use Magento\Signifyd\Api\CaseRepositoryInterface;

/**
 * Generates message based on previous and current Case scores.
 * @since 2.2.0
 */
class CaseRescore implements GeneratorInterface
{
    /**
     * @var CaseRepositoryInterface
     * @since 2.2.0
     */
    private $caseRepository;

    /**
     * CaseRescore constructor.
     *
     * @param CaseRepositoryInterface $caseRepository
     * @since 2.2.0
     */
    public function __construct(CaseRepositoryInterface $caseRepository)
    {
        $this->caseRepository = $caseRepository;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function generate(array $data)
    {
        if (empty($data['caseId'])) {
            throw new GeneratorException(__('The "%1" should not be empty.', 'caseId'));
        }

        $caseEntity = $this->caseRepository->getByCaseId($data['caseId']);

        if ($caseEntity === null) {
            throw new GeneratorException(__('Case entity not found.'));
        }

        return __(
            'Case Update: New score for the order is %1. Previous score was %2.',
            !empty($data['score']) ? $data['score'] : 0,
            $caseEntity->getScore()
        );
    }
}
