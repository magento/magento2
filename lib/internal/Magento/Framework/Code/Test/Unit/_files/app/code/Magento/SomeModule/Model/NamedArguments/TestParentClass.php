<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SomeModule\Model\NamedArguments;

class TestParentClass
{
    /**
     * @var \stdClass
     */
    protected \stdClass $stdClassObject;

    /**
     * @var array
     */
    protected array $arrayVariable;

    /**
     * @param \stdClass $stdClassObject
     * @param array $arrayVariable
     */
    public function __construct(\stdClass $stdClassObject, array $arrayVariable)
    {
        $this->stdClassObject = $stdClassObject;
        $this->arrayVariable = $arrayVariable;
    }
}
