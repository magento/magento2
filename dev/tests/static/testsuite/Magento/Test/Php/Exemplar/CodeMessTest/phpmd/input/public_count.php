<?php

/**
 * Class that violates the allowed public members count.
 *
 * 'ExcessivePublicCount' rule intersects with the 'TooManyFields' and 'TooManyMethods', so check only the needed one.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
abstract class Foo
{
    public $field01;
    public $field02;
    public $field03;
    public $field04;
    public $field05;
    public $field06;
    public $field07;
    public $field08;
    public $field09;
    public $field10;
    public $field11;
    public $field12;
    public $field13;
    public $field14;
    public $field15;
    public $field16;
    public $field17;
    public $field18;
    public $field19;
    public $field20;
    public $field21;
    public $field22;
    public $field23;
    public $field24;
    public $field25;
    abstract public function method01();
    abstract public function method02();
    abstract public function method03();
    abstract public function method04();
    abstract public function method05();
    abstract public function method06();
    abstract public function method07();
    abstract public function method08();
    abstract public function method09();
    abstract public function method10();
    abstract public function method11();
    abstract public function method12();
    abstract public function method13();
    abstract public function method14();
    abstract public function method15();
    abstract public function method16();
    abstract public function method17();
    abstract public function method18();
    abstract public function method19();
    abstract public function method20();
}
