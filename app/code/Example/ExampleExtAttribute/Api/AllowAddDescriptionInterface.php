<?php


namespace Example\ExampleExtAttribute\Api;


interface AllowAddDescriptionInterface
{
    const VALUE = 0;

    /**
     * @return string
     */
    public function getValue();

    /**
     *
     * @return $this
     */
    public function setValue($value);

}
