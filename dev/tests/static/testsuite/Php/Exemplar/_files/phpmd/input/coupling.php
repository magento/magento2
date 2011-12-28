<?php

class Foo01 {}

class Foo02 extends Foo01 {}

class Foo03 extends Foo01 {}

class Foo04 extends Foo01 {}

class Foo05 extends Foo01 {}

class Foo06 extends Foo01 {}

class Foo07 extends Foo01 {}

class Foo08 extends Foo01 {}

class Foo extends Foo01
{
    /**
     * coupling = 1
     * @var Foo02
     */
    protected $field02;

    /**
     * coupling = 2
     * @var Foo03
     */
    protected $field03;

    public function setFoo04(Foo04 $foo) // coupling = 3
    {
        $this->field04 = $foo;
    }

    public function setFoo05(Foo05 $foo) // coupling = 4
    {
        $this->field05 = $foo;
    }

    /**
     * coupling = 5
     * @param Foo06 $foo
     */
    public function setFoo06(Foo06 $foo)
    {
        $this->field06 = $foo;
    }

    public function setFoo07(Foo07 $foo) // coupling = 6
    {
        $this->field07 = $foo;
    }

    public function getNewFoo08()
    {
        return new Foo08; // coupling = 7
    }

    public function getNewStdClass()
    {
        return new stdClass(); // coupling = 8
    }

    /**
     * coupling = 12
     * @return SplObjectStorage
     * @throws OutOfRangeException
     * @throws InvalidArgumentException
     * @throws ErrorException
     */
    public function process(Iterator $iterator) // coupling = 13
    {
        $iterator->rewind();
    }
}
