<?php

/**
 * Class that violates the allowed method number
 */
abstract class Foo
{
    private function method01()
    {
        return 'something';
    }

    protected function method02()
    {
        /* Use private method to not consider it unused */
        $this->method01();
    }

    abstract protected function method03();

    abstract protected function method04();

    abstract protected function method05();

    abstract protected function method06();

    abstract protected function method07();

    abstract protected function method08();

    abstract protected function method09();

    abstract protected function method10();

    abstract protected function method11();

    abstract protected function method12();

    abstract protected function method13();

    abstract protected function method14();

    abstract protected function method15();

    abstract protected function method16();

    abstract protected function method17();

    abstract protected function method18();

    abstract protected function method19();

    abstract protected function method20();

    abstract protected function method21();

    abstract protected function method22();

    abstract protected function method23();

    abstract protected function method24();

    abstract protected function method25();

    abstract protected function method26();

    abstract protected function method27();

    abstract protected function method28();

    abstract protected function method29();

    abstract protected function method30();

    abstract protected function method31();

    abstract protected function method32();

    abstract protected function method33();

    abstract protected function method34();

    abstract protected function method35();

    abstract protected function method36();

    abstract protected function method37();

    abstract protected function method38();

    abstract protected function method39();

    abstract protected function method40();

    abstract protected function method41();

    abstract protected function method42();

    abstract protected function method43();

    abstract protected function method44();

    abstract protected function method45();

    abstract protected function method46();

    abstract protected function method47();

    abstract protected function method48();

    abstract protected function method49();

    abstract protected function method50();

    abstract public function method51();
}
