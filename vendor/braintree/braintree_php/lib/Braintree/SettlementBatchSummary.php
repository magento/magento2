<?php
class Braintree_SettlementBatchSummary extends Braintree
{
    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    /**
     * @ignore
     */
    protected function _initialize($attributes)
    {
        $this->_attributes = $attributes;
    }

    public function records()
    {
        return $this->_attributes['records'];
    }


    // static methods redirecting to gateway

    public static function generate($settlement_date, $groupByCustomField = NULL)
    {
        return Braintree_Configuration::gateway()->settlementBatchSummary()->generate($settlement_date, $groupByCustomField);
    }
}
