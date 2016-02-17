<?php
/**
 * Braintree Successful Result
 *
 * A Successful Result will be returned from gateway methods when
 * validations pass. It will provide access to the created resource.
 *
 * For example, when creating a customer, Braintree_Result_Successful will
 * respond to <b>customer</b> like so:
 *
 * <code>
 * $result = Braintree_Customer::create(array('first_name' => "John"));
 * if ($result->success) {
 *     // Braintree_Result_Successful
 *     echo "Created customer {$result->customer->id}";
 * } else {
 *     // Braintree_Result_Error
 * }
 * </code>
 *
 *
 * @package    Braintree
 * @subpackage Result
 * @copyright  2014 Braintree, a division of PayPal, Inc.
 */
class Braintree_Result_Successful extends Braintree_Instance
{
    /**
     *
     * @var boolean always true
     */
    public $success = true;
    /**
     *
     * @var string stores the internal name of the object providing access to
     */
    private $_returnObjectNames;

    /**
     * @ignore
     * @param string $classToReturn name of class to instantiate
     */
    public function __construct($objsToReturn = array(), $propertyNames = array())
    {
        // Sanitize arguments (preserves backwards compatibility)
        if (!is_array($objsToReturn)) { $objsToReturn = array($objsToReturn); }
        if (!is_array($propertyNames)) { $propertyNames = array($propertyNames); }

        $objects = $this->_mapPropertyNamesToObjsToReturn($propertyNames, $objsToReturn);
        $this->_attributes = array();
        $this->_returnObjectNames = array();

        foreach ($objects as $propertyName => $objToReturn) {

            // save the name for indirect access
            array_push($this->_returnObjectNames, $propertyName);

            // create the property!
            $this->$propertyName = $objToReturn;
        }
    }

   /**
    *
    * @ignore
    * @return string string representation of the object's structure
    */
   public function __toString()
   {
       $objects = array();
       foreach ($this->_returnObjectNames as $returnObjectName) {
           array_push($objects, $this->$returnObjectName);
       }
       return __CLASS__ . '[' . implode(', ', $objects) . ']';
   }

   private function _mapPropertyNamesToObjsToReturn($propertyNames, $objsToReturn) {
       if(count($objsToReturn) != count($propertyNames)) {
           $propertyNames = array();
           foreach ($objsToReturn as $obj) {
               array_push($propertyNames, Braintree_Util::cleanClassName(get_class($obj)));
           }
       }
       return array_combine($propertyNames, $objsToReturn);
   }
}
