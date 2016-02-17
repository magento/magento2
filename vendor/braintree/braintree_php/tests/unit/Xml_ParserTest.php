<?php

require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_Xml_ParserTest extends PHPUnit_Framework_TestCase
{
    function testTypeCastIntegers()
    {
        $array = Braintree_Xml::buildArrayFromXml('<root><foo type="integer">123</foo></root>');
        $this->assertEquals($array, array('root' => array('foo' => 123)));

    }

    function testDashesUnderscores()
    {
        $xml =<<<XML
        <root>
          <dash-es />
          <under_scores />
        </root>
XML;

        $array = Braintree_Xml::buildArrayFromXml($xml);
        $this->assertEquals(array('root' =>
            array('dashEs' => '', 'underScores' => '')), $array);
    }

    function testCustomFieldsUnderscore()
    {
        $xml =<<<XML
        <root>
          <custom-fields>
            <with-dashes>convert to underscore</with-dashes>
          </custom-fields>
        </root>
XML;

        $array = Braintree_Xml::buildArrayFromXml($xml);
        $this->assertEquals(array('root' =>
            array('customFields' => array('with_dashes' => 'convert to underscore'))), $array);
    }

    function testNullOrEmptyString()
    {
        $xml = <<<XML
        <root>
          <a_nil_value nil="true"></a_nil_value>
          <an_empty_string></an_empty_string>
        </root>
XML;
        $array = Braintree_Xml::buildArrayFromXml($xml);
        $this->assertEquals(array('root' =>
            array('aNilValue' => null, 'anEmptyString' => '')), $array);
    }

    function testTypeCastsDatetimes()
    {
        $xml = <<<XML
        <root>
          <created-at type="datetime">2009-10-28T10:19:49Z</created-at>
        </root>
XML;
        $array = Braintree_Xml::buildArrayFromXml($xml);
        date_default_timezone_set('UTC');
        $dateTime = new DateTime('2009-10-28T10:19:49', new DateTimeZone('UTC'));
        $this->assertEquals(array('root' => array('createdAt' => $dateTime)), $array);
        $this->assertInstanceOf('DateTime', $array['root']['createdAt']);
    }

    function testTypeCastsDates()
    {
        $xml = <<<XML
        <root>
          <some-date type="date">2009-10-28</some-date>
        </root>
XML;
        $array = Braintree_Xml::buildArrayFromXml($xml);
        date_default_timezone_set('UTC');
        $dateTime = new DateTime('2009-10-28', new DateTimeZone('UTC'));
        $this->assertEquals(array('root' => array('someDate' => $dateTime)), $array);
    }

    function testBuildsArray()
    {
        $xml = <<<XML
        <root>
          <customers type="array">
            <customer><name>Adam</name></customer>
            <customer><name>Ben</name></customer>
          </customers>
        </root>
XML;
        $array = Braintree_Xml::buildArrayFromXml($xml);
        $this->assertEquals(array('root' =>
            array('customers' =>
                    array(array('name' => 'Adam'),
                           array('name' => 'Ben'))
                    )
            ), $array
        );

    }

    function testReturnsBoolean()
    {
        $xml = <<<XML
        <root>
          <casted-true type="boolean">true</casted-true>
          <casted-one type="boolean">1</casted-one>
          <casted-false type="boolean">false</casted-false>
          <casted-anything type="boolean">anything</casted-anything>
          <uncasted-true>true</uncasted-true>
        </root>
XML;
         $array = Braintree_Xml::buildArrayFromXml($xml);
         $this->assertEquals(
            array('root' =>
              array('castedTrue' => true,
                    'castedOne' => true,
                    'castedFalse' => false,
                    'castedAnything' => false,
                    'uncastedTrue' => 'true')
        ), $array);

    }

    function testEmptyArrayAndNestedElements()
    {
        $xml = <<<XML
        <root>
          <nested-values>
            <value>1</value>
          </nested-values>
          <no-values type="array"/>
        </root>
XML;

         $array = Braintree_Xml::buildArrayFromXml($xml);
         $this->assertEquals(
              array('root' => array(
                  'noValues' => array(),
                   'nestedValues' => array(
                       'value' => 1
                   )
              )
         ), $array);
    }

    function testParsingNilEqualsTrueAfterArray()
    {
        $xml = <<<XML
        <root>
          <customer>
            <first-name>Dan</first-name>
          </customer>
          <blank nil="true" />
        </root>
XML;
        $array = Braintree_Xml::buildArrayFromXml($xml);
        $this->assertEquals(null, $array['root']['blank']);

    }

    function testTransactionParsingNil()
    {
        $xml = <<<XML
<transaction>
  <id>8ysndw</id>
  <status>settled</status>
  <type>sale</type>
  <currency>USD</currency>
  <amount>1.00</amount>
  <merchant-account-id>default</merchant-account-id>
  <order-id nil="true"></order-id>
  <channel nil="true"></channel>
  <created-at type="datetime">2010-04-01T19:32:23Z</created-at>
  <updated-at type="datetime">2010-04-02T08:05:35Z</updated-at>
  <customer>
    <id nil="true"></id>
    <first-name>First</first-name>
    <last-name>Last</last-name>
    <company nil="true"></company>
    <email></email>
    <website nil="true"></website>
    <phone nil="true"></phone>
    <fax nil="true"></fax>
  </customer>
  <billing>
    <id nil="true"></id>
    <first-name nil="true"></first-name>
    <last-name nil="true"></last-name>
    <company>Widgets Inc</company>
    <street-address>1234 My Street</street-address>
    <extended-address>Apt 1</extended-address>
    <locality>Ottawa</locality>
    <region>ON</region>
    <postal-code>K1C2N6</postal-code>
    <country-name>Canada</country-name>
  </billing>
  <refund-id nil="true"></refund-id>
  <shipping>
    <id nil="true"></id>
    <first-name nil="true"></first-name>
    <last-name nil="true"></last-name>
    <company nil="true"></company>
    <street-address nil="true"></street-address>
    <extended-address nil="true"></extended-address>
    <locality nil="true"></locality>
    <region nil="true"></region>
    <postal-code nil="true"></postal-code>
    <country-name nil="true"></country-name>
  </shipping>
  <custom-fields>
  </custom-fields>
  <avs-error-response-code nil="true"></avs-error-response-code>
  <avs-postal-code-response-code>M</avs-postal-code-response-code>
  <avs-street-address-response-code>M</avs-street-address-response-code>
  <cvv-response-code>M</cvv-response-code>
  <processor-authorization-code>13390</processor-authorization-code>
  <processor-response-code>1000</processor-response-code>
  <processor-response-text>Approved</processor-response-text>
  <credit-card>
    <token nil="true"></token>
    <bin>510510</bin>
    <last-4>5100</last-4>
    <card-type>MasterCard</card-type>
    <expiration-month>09</expiration-month>
    <expiration-year>2011</expiration-year>
    <customer-location>US</customer-location>
    <cardholder-name nil="true"></cardholder-name>
  </credit-card>
  <status-history type="array">
    <status-event>
      <timestamp type="datetime">2010-04-01T19:32:24Z</timestamp>
      <status>authorized</status>
      <amount>1.00</amount>
      <user>dmanges-am</user>
      <transaction-source>API</transaction-source>
    </status-event>
    <status-event>
      <timestamp type="datetime">2010-04-01T19:32:25Z</timestamp>
      <status>submitted_for_settlement</status>
      <amount>1.00</amount>
      <user>dmanges-am</user>
      <transaction-source>API</transaction-source>
    </status-event>
    <status-event>
      <timestamp type="datetime">2010-04-02T08:05:36Z</timestamp>
      <status>settled</status>
      <amount>1.00</amount>
      <user nil="true"></user>
      <transaction-source></transaction-source>
    </status-event>
  </status-history>
</transaction>
XML;
        $array = Braintree_Xml::buildArrayFromXml($xml);
        $this->assertEquals(null, $array['transaction']['avsErrorResponseCode']);
        $this->assertEquals(null, $array['transaction']['refundId']);
        $this->assertEquals(null, $array['transaction']['orderId']);
        $this->assertEquals(null, $array['transaction']['channel']);
        $this->assertEquals(null, $array['transaction']['customer']['fax']);
        $this->assertEquals(null, $array['transaction']['creditCard']['token']);
        $this->assertEquals(null, $array['transaction']['creditCard']['cardholderName']);
        $this->assertEquals('First', $array['transaction']['customer']['firstName']);
        $this->assertEquals('Approved', $array['transaction']['processorResponseText']);

    }

    function testParsingWithNodeHavingSameNameAsNodesDirectlyUnderCollection()
    {
        $xml = <<<END
<foos type="collection">
  <page-size>50</page-size>
  <bar>
    <baz>one</baz>
  </bar>
  <bar>
    <baz>two</baz>
    <bar>bug was here</bar>
  </bar>
</foos>
END;
        $array = Braintree_Xml::buildArrayFromXml($xml);
        $this->assertEquals(array('baz' => 'two', 'bar' => 'bug was here'), $array['foos']['bar'][1]);
    }

    function testParsingCreditCardSearchResults()
    {
        $xml = <<<END
<payment-methods type="collection">
  <current-page-number type="integer">1</current-page-number>
  <page-size type="integer">50</page-size>
  <total-items type="integer">8</total-items>
  <credit-card>
    <bin>411111</bin>
    <cardholder-name>John Doe</cardholder-name>
    <card-type>Visa</card-type>
    <created-at type="datetime">2010-07-02T15:50:51Z</created-at>
    <customer-id>589636</customer-id>
    <default type="boolean">true</default>
    <expiration-month>05</expiration-month>
    <expiration-year>2009</expiration-year>
    <expired type="boolean">true</expired>
    <customer-location>US</customer-location>
    <last-4>1111</last-4>
    <subscriptions type="array"/>
    <token>22pb</token>
    <updated-at type="datetime">2010-07-02T15:50:51Z</updated-at>
  </credit-card>
  <credit-card>
    <bin>411111</bin>
    <cardholder-name></cardholder-name>
    <card-type>Visa</card-type>
    <created-at type="datetime">2010-07-02T15:52:09Z</created-at>
    <customer-id>613603</customer-id>
    <default type="boolean">false</default>
    <expiration-month>05</expiration-month>
    <expiration-year>2009</expiration-year>
    <expired type="boolean">true</expired>
    <customer-location>US</customer-location>
    <last-4>1111</last-4>
    <subscriptions type="array">
      <subscription>
        <id>hzjh8b</id>
        <price>54.32</price>
        <plan-id>integration_trialless_plan</plan-id>
        <first-billing-date type="date">2010-07-02</first-billing-date>
        <next-billing-date type="date">2010-08-02</next-billing-date>
        <billing-period-start-date type="date">2010-07-02</billing-period-start-date>
        <billing-period-end-date type="date">2010-08-01</billing-period-end-date>
        <merchant-account-id>sandbox_credit_card</merchant-account-id>
        <trial-period type="boolean">false</trial-period>
        <status>Active</status>
        <failure-count type="integer">0</failure-count>
        <payment-method-token>3wx6</payment-method-token>
        <trial-duration nil="true"></trial-duration>
        <trial-duration-unit nil="true"></trial-duration-unit>
        <transactions type="array">
          <transaction>
            <id>2dpk76</id>
            <status>submitted_for_settlement</status>
            <type>sale</type>
            <currency-iso-code>USD</currency-iso-code>
            <amount>54.32</amount>
            <merchant-account-id>sandbox_credit_card</merchant-account-id>
            <order-id nil="true"></order-id>
            <channel nil="true"></channel>
            <created-at type="datetime">2010-07-02T15:52:09Z</created-at>
            <updated-at type="datetime">2010-07-02T15:52:09Z</updated-at>
            <customer>
              <id>613603</id>
              <first-name>Mike</first-name>
              <last-name>Jones</last-name>
              <company nil="true"></company>
              <email nil="true"></email>
              <website nil="true"></website>
              <phone nil="true"></phone>
              <fax nil="true"></fax>
            </customer>
            <billing>
              <id nil="true"></id>
              <first-name nil="true"></first-name>
              <last-name nil="true"></last-name>
              <company nil="true"></company>
              <street-address nil="true"></street-address>
              <extended-address nil="true"></extended-address>
              <locality nil="true"></locality>
              <region nil="true"></region>
              <postal-code nil="true"></postal-code>
              <country-name nil="true"></country-name>
              <country-code-alpha2 nil="true"></country-code-alpha2>
              <country-code-alpha3 nil="true"></country-code-alpha3>
              <country-code-numeric nil="true"></country-code-numeric>
            </billing>
            <refund-id nil="true"></refund-id>
            <refunded-transaction-id nil="true"></refunded-transaction-id>
            <shipping>
              <id nil="true"></id>
              <first-name nil="true"></first-name>
              <last-name nil="true"></last-name>
              <company nil="true"></company>
              <street-address nil="true"></street-address>
              <extended-address nil="true"></extended-address>
              <locality nil="true"></locality>
              <region nil="true"></region>
              <postal-code nil="true"></postal-code>
              <country-name nil="true"></country-name>
              <country-code-alpha2 nil="true"></country-code-alpha2>
              <country-code-alpha3 nil="true"></country-code-alpha3>
              <country-code-numeric nil="true"></country-code-numeric>
            </shipping>
            <custom-fields>
            </custom-fields>
            <avs-error-response-code nil="true"></avs-error-response-code>
            <avs-postal-code-response-code>I</avs-postal-code-response-code>
            <avs-street-address-response-code>I</avs-street-address-response-code>
            <cvv-response-code>I</cvv-response-code>
            <gateway-rejection-reason nil="true"></gateway-rejection-reason>
            <processor-authorization-code>9ZR5QB</processor-authorization-code>
            <processor-response-code>1000</processor-response-code>
            <processor-response-text>Approved</processor-response-text>
            <credit-card>
              <token>sb8w</token>
              <bin>411111</bin>
              <last-4>1111</last-4>
              <card-type>Visa</card-type>
              <expiration-month>05</expiration-month>
              <expiration-year>2010</expiration-year>
              <customer-location>US</customer-location>
              <cardholder-name></cardholder-name>
            </credit-card>
            <status-history type="array">
              <status-event>
                <timestamp type="datetime">2010-07-02T15:52:09Z</timestamp>
                <status>authorized</status>
                <amount>54.32</amount>
                <user>merchant</user>
                <transaction-source>Recurring</transaction-source>
              </status-event>
              <status-event>
                <timestamp type="datetime">2010-07-02T15:52:09Z</timestamp>
                <status>submitted_for_settlement</status>
                <amount>54.32</amount>
                <user>merchant</user>
                <transaction-source>Recurring</transaction-source>
              </status-event>
            </status-history>
            <subscription-id>hzjh8b</subscription-id>
          </transaction>
        </transactions>
      </subscription>
    </subscriptions>
    <token>3wx6</token>
    <updated-at type="datetime">2010-07-02T15:52:09Z</updated-at>
  </credit-card>
</payment-methods>
END;
        $array = Braintree_Xml::buildArrayFromXml($xml);
        $creditCards = $array['paymentMethods']['creditCard'];
        $creditCardWithSubscription = $creditCards[1];
        $transaction = $creditCardWithSubscription['subscriptions'][0]['transactions'][0];
        $this->assertEquals('411111', $transaction['creditCard']['bin']);
        $this->assertEquals('1111', $transaction['creditCard']['last4']);
        $this->assertEquals('Visa', $transaction['creditCard']['cardType']);
    }

    function xmlAndBack($array)
    {
        $xml = Braintree_Xml::buildXmlFromArray($array);
        return Braintree_Xml::buildArrayFromXml($xml);

    }

    function testSimpleCaseRoundtrip()
    {
        $array = array('root' => array(
            'foo' => 'fooValue',
            'bar' => 'barValue')
            );

        $array2 = $this->xmlAndBack($array);
        $this->assertEquals($array, $array2);
    }

    function testArrayRoundtrip()
    {
        $array = array('root' => array (
            'items' => array(
                array('name' => 'first'),
                array('name' => 'second'),
            )
        ));
        $array2 = $this->xmlAndBack($array);
        $this->assertEquals($array, $array2);
    }

    function testBooleanRoundtrip()
    {
        $array = array('root' => array(
            'stringTrue' => true,
            'boolTrue' => true,
            'stringFalse' => false,
            'boolFalse' => false,
        ));
        $array2 = $this->xmlAndBack($array);
        $this->assertEquals($array, $array2);

    }
    function testTimestampRoundtrip()
    {
        date_default_timezone_set('UTC');
        $array = array('root' => array(
           'aTimestamp' => date('D M d H:i:s e Y', mktime(1, 2, 3, 10, 28, 2009)),
        ));
        $array2 = $this->xmlAndBack($array);
        $this->assertEquals($array, $array2);

    }

    function testNullvsEmptyStringToXml()
    {
        $array = array('root' => array(
            'anEmptyString' => '',
            'aNullValue' => null,
            ));
        $xml = Braintree_Xml::buildXmlFromArray($array);
        $xml2 =<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
 <an-empty-string></an-empty-string>
 <a-null-value nil="true"></a-null-value>
</root>

XML;

        $this->assertEquals($xml, $xml2);
    }

    function testIncludesTheEncodingRoundtrip()
    {
        $array = array('root' => array(
           'root' => 'bar',
        ));
        $xml = Braintree_Xml::buildXmlFromArray($array);
        $this->assertRegExp('<\?xml version=\"1.0\" encoding=\"UTF-8\"\?>', $xml);

    }

    function testRootNodeAndStringRoundtrip()
    {
        $array = array('id' => '123');
        $array2 = $this->xmlAndBack($array);
        $this->assertEquals($array, $array2);
    }
}
