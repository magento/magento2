<?php
final class Braintree_Disbursement extends Braintree
{
    private $_merchantAccount;

    protected function _initialize($disbursementAttribs)
    {
        $this->_attributes = $disbursementAttribs;
        $this->merchantAccountDetails = $disbursementAttribs['merchantAccount'];

        if (isset($disbursementAttribs['merchantAccount'])) {
            $this->_set('merchantAccount',
                Braintree_MerchantAccount::factory($disbursementAttribs['merchantAccount'])
            );
        }
    }

    public function transactions()
    {
        $collection = Braintree_Transaction::search(array(
            Braintree_TransactionSearch::ids()->in($this->transactionIds)
        ));

        return $collection;
    }

    public static function factory($attributes)
    {
        $instance = new self();
        $instance->_initialize($attributes);
        return $instance;
    }

    public function  __toString()
    {
        $display = array(
            'id', 'merchantAccountDetails', 'exceptionMessage', 'amount',
            'disbursementDate', 'followUpAction', 'retry', 'success',
            'transactionIds'
            );

        $displayAttributes = array();
        foreach ($display AS $attrib) {
            $displayAttributes[$attrib] = $this->$attrib;
        }
        return __CLASS__ . '[' .
                Braintree_Util::attributesToString($displayAttributes) .']';
    }
}
