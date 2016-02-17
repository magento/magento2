<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_TestResource
{
    public function lookup($id) {
        return Braintree_ResourceCollectionTest::$values[intval($id)];
    }

    public function fetch($ids)
    {

        return array_map("Braintree_TestResource::lookup", $ids);
    }
}

class Braintree_ResourceCollectionTest extends PHPUnit_Framework_TestCase
{
    public static $values = array("a", "b", "c", "d", "e");

    function testIterateOverResults()
    {

        $response = array(
            'searchResults' => array(
                'pageSize' => 2,
                'ids' => array('0', '1', '2', '3', '4')
            )
        );

        $object = new Braintree_TestResource();
        $pager = array(
            'object' => $object,
            'method' => 'fetch',
            'methodArgs' => array()
        );

        $collection = new Braintree_ResourceCollection($response, $pager);

        $count = 0;
        $index = 0;
        foreach ($collection as $value)
        {
            $this->assertEquals(Braintree_ResourceCollectionTest::$values[$index], $value);
            $index += 1;
            $count += 1;
        }

        $this->assertEquals(5, $count);
    }

    function testDoesntIterateWhenNoResults()
    {

        $response = array(
            'searchResults' => array(
                'pageSize' => 2,
                'ids' => array()
            )
        );

        $object = new Braintree_TestResource();
        $pager = array(
            'object' => $object,
            'method' => 'fetch',
            'methodArgs' => array()
        );

        $collection = new Braintree_ResourceCollection($response, $pager);

        $count = 0;
        $index = 0;
        foreach ($collection as $value)
        {
            $index += 1;
            $count += 1;
            break;
        }

        $this->assertEquals(0, $count);
        $this->assertEquals(0, $index);
    }
}
