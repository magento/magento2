<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SomeModule\Model\NamedArguments;

require_once __DIR__ . '/TestParentClass.php';

class TestMixedParameters extends TestParentClass
{
    /**
     * @param \stdClass $stdClassObject
     * @param array $arrayVariable
     */
    public function __construct(\stdClass $stdClassObject, array $arrayVariable)
    {
        parent::__construct($stdClassObject, arrayVariable: $arrayVariable);
    }
}
