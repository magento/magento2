<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\MessageGenerators;

use Magento\Signifyd\Model\MessageGeneratorInterface;

/**
 * Generates message about new guarantee creation for a Signifyd case.
 */
class GuaranteeCreation implements MessageGeneratorInterface
{
    /**
     * @inheritdoc
     */
    public function generate(array $data)
    {
        return __('Case Update: Case is submitted for guarantee.');
    }
}
