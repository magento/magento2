<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\MessageGenerators;

use Magento\Signifyd\Model\MessageGeneratorException;
use Magento\Signifyd\Model\MessageGeneratorInterface;
use Magento\Signifyd\Model\Validators\CaseIdValidator;

/**
 * Generates message for created Signifyd case.
 */
class CaseCreation implements MessageGeneratorInterface
{
    /**
     * @var CaseIdValidator
     */
    private $caseIdValidator;

    /**
     * CaseCreation constructor.
     *
     * @param CaseIdValidator $caseIdValidator
     */
    public function __construct(CaseIdValidator $caseIdValidator)
    {
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

        return __('Signifyd Case %1 has been created for order.', $data['caseId']);
    }
}
