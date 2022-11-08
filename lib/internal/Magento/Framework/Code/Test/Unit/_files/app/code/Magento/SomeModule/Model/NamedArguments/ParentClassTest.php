<?php

namespace Magento\SomeModule\Model\NamedArguments;

class ParentClassTest
{
    /**
     * @var stdClass
     */
    protected $stdClassObject;

    /**
     * @var array
     */
    protected $arrayVariable;

    /**
     * @param stdClass $stdClassObject
     * @param array $arrayVariable
     */
    public function __construct(\stdClass $stdClassObject, array $arrayVariable)
    {
        $this->stdClassObject = $stdClassObject;
        $this->arrayVariable = $arrayVariable;
    }
}
