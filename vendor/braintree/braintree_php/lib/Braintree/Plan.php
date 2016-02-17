<?php
class Braintree_Plan extends Braintree
{
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);

        return $instance;
    }

    protected function _initialize($attributes)
    {
        $this->_attributes = $attributes;

        $addOnArray = array();
        if (isset($attributes['addOns'])) {
            foreach ($attributes['addOns'] AS $addOn) {
                $addOnArray[] = Braintree_AddOn::factory($addOn);
            }
        }
        $this->_attributes['addOns'] = $addOnArray;

        $discountArray = array();
        if (isset($attributes['discounts'])) {
            foreach ($attributes['discounts'] AS $discount) {
                $discountArray[] = Braintree_Discount::factory($discount);
            }
        }
        $this->_attributes['discounts'] = $discountArray;

        $planArray = array();
        if (isset($attributes['plans'])) {
            foreach ($attributes['plans'] AS $plan) {
                $planArray[] = Braintree_Plan::factory($plan);
            }
        }
        $this->_attributes['plans'] = $planArray;
    }


    // static methods redirecting to gateway

    public static function all()
    {
        return Braintree_Configuration::gateway()->plan()->all();
    }
}
