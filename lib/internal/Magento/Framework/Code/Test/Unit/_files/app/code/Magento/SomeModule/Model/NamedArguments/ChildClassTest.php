<?php
declare(strict_types=1);
namespace Magento\SomeModule\Model\NamedArguments;

require_once __DIR__ . '/ParentClassTest.php';

class ChildClassTest extends ParentClassTest
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
        parent::__construct(arrayVariable: $arrayVariable, stdClassObject: $stdClassObject);
    }
}
