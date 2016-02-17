<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_MultipleValueNodeTest extends PHPUnit_Framework_TestCase
{
    function testIs()
    {
        $node = new Braintree_MultipleValueNode('field');
        $node->is('value');
        $this->assertEquals(array('value'), $node->toParam());
    }

    function testIn()
    {
        $node = new Braintree_MultipleValueNode('field');
        $node->in(array('firstValue', 'secondValue'));
        $this->assertEquals(array('firstValue', 'secondValue'), $node->toParam());
    }
}
