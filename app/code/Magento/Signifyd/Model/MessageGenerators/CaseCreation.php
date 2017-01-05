<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\MessageGenerators;

use Magento\Signifyd\Model\MessageGeneratorException;
use Magento\Signifyd\Model\MessageGeneratorInterface;
use Magento\Signifyd\Model\Validators\CaseDataValidator;

/**
 * Generates message for created Signifyd case.
 */
class CaseCreation implements MessageGeneratorInterface
{
    /**
     * @var CaseDataValidator
     */
    private $caseDataValidator;

    /**
     * CaseCreation constructor.
     *
     * @param CaseDataValidator $caseDataValidator
     */
    public function __construct(CaseDataValidator $caseDataValidator)
    {
        $this->caseDataValidator = $caseDataValidator;
    }

    /**
     * @inheritdoc
     */
    public function generate(array $data)
    {
        if (!$this->caseDataValidator->validate($data)) {
            throw new MessageGeneratorException(__('The "%1" should not be empty.', 'caseId'));
        }

        return __('Signifyd Case %1 has been created for order.', $data['caseId']);
    }
}
