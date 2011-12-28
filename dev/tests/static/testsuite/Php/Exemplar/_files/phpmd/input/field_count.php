<?php

/**
 * Class that violates the allowed field number
 */
abstract class Foo
{
    private $field01;
    protected $field02;
    protected $field03;
    protected $field04;
    protected $field05;
    protected $field06;
    protected $field07;
    protected $field08;
    protected $field09;
    protected $field10;
    public $field11;
    public $field12;
    public $field13;
    public $field14;
    public $field15;
    public $field16;

    public function getField01()
    {
        return $this->field01;
    }
}
