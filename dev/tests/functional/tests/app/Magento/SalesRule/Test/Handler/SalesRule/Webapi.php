<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Handler\SalesRule;

use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\System\Event\EventManagerInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\WebapiDecorator;
use Magento\Mtf\Handler\Webapi as AbstractWebapi;
use Magento\SalesRule\Test\Fixture\SalesRule;

/**
 * Create new sales rule via webapi.
 */
class Webapi extends AbstractWebapi implements SalesRuleInterface
{
    /**
     * Sales rule instance.
     *
     * @var SalesRule
     */
    protected $fixture;

    /**
     * Prepared data for creating sales rule.
     *
     * @var array
     */
    protected $data;

    /**
     * Curl handler instance.
     *
     * @var Curl
     */
    protected $handlerCurl;

    /**
     * List fields that only relate to coupon.
     *
     * @var array
     */
    protected $couponFields = [
        'coupon_code'
    ];

    /**
     * Attributes that has a setter while creating sales rule using web api.
     *
     * @var array
     */
    protected $basicAttributes = [
        "rule_id",
        "name",
        "store_labels",
        "description",
        "website_ids",
        "customer_group_ids",
        "from_date",
        "to_date",
        "uses_per_customer",
        "is_active",
        "condition",
        "action_condition",
        "stop_rules_processing",
        "is_advanced",
        "product_ids",
        "sort_order",
        "simple_action",
        "discount_amount",
        "discount_qty",
        "discount_step",
        "apply_to_shipping",
        "times_used",
        "is_rss",
        "coupon_type",
        "use_auto_generation",
        "uses_per_coupon",
        "simple_free_shipping",
    ];

    /**
     * @constructor
     * @param DataInterface $configuration
     * @param EventManagerInterface $eventManager
     * @param WebapiDecorator $webapiTransport
     * @param Curl $handlerCurl
     */
    public function __construct(
        DataInterface $configuration,
        EventManagerInterface $eventManager,
        WebapiDecorator $webapiTransport,
        Curl $handlerCurl
    ) {
        parent::__construct($configuration, $eventManager, $webapiTransport);
        $this->handlerCurl = $handlerCurl;
    }

    /**
     * Post request for creating sales rule.
     *
     * @param FixtureInterface|null $fixture [optional]
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $this->prepareData($fixture);
        $url = $_ENV['app_frontend_url'] . 'rest/V1/salesRules';

        $this->webapiTransport->write($url, $data);
        $response = json_decode($this->webapiTransport->read(), true);
        $this->webapiTransport->close();

        if (empty($response['rule_id'])) {
            $this->eventManager->dispatchEvent(['webapi_failed'], [$response]);
            throw new \Exception('sales rule creation by webapi handler was not successful!');
        }

        $this->createCoupon($response['rule_id']);

        return ['rule_id' => $response['rule_id']];
    }

    /**
     * Prepare sales rule data for webapi request.
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    public function prepareData(FixtureInterface $fixture)
    {
        $this->fixture = $fixture;
        $this->data = $this->handlerCurl->prepareData($fixture);

        $this->prepareRuleInformation();
        $this->prepareConditions();
        $this->prepareActions();
        $this->prepareLabels();
        unset($this->data['rule']);
        $this->prepareExtensionAttributes();

        return ['rule' => $this->data];
    }

    /**
     * Preparation of "Rule Information" tab.
     *
     * @return void
     */
    protected function prepareRuleInformation()
    {
        $this->data = array_diff_key($this->data, array_flip($this->couponFields));
        $this->data['coupon_type'] = strtoupper(str_replace(' ', '_', $this->fixture->getCouponType()));
    }

    /**
     * Preparation of "Conditions" tab.
     *
     * @return void
     */
    protected function prepareConditions()
    {
        if (isset($this->data['rule']['conditions'])) {
            $this->data['condition'] = $this->convertCondition($this->data['rule']['conditions'])[0];
        }
    }

    /**
     * Preparation of "Actions" tab.
     *
     * @return void
     */
    protected function prepareActions()
    {
        if (isset($this->data['rule']['actions'])) {
            $this->data['action_condition'] = $this->convertCondition($this->data['rule']['actions'])[0];
        }
    }

    /**
     * Preparation of "Labels" tab.
     *
     * @return void
     */
    protected function prepareLabels()
    {
        if (isset($this->data['store_labels'])) {
            foreach ($this->data['store_labels'] as $storeId => $label) {
                $this->data['store_labels'][$storeId] = [
                    'store_id' => $storeId,
                    'store_label' => $label
                ];
            }
        }
    }

    /**
     * Create coupon related to sales rule .
     *
     * @param int $ruleId
     * @return void
     * @throws \Exception
     */
    protected function createCoupon($ruleId)
    {
        if (!$this->fixture->hasData('coupon_code')) {
            return;
        }

        $url = $_ENV['app_frontend_url'] . 'rest/V1/coupons';
        $data = [
            'coupon' => array_filter([
                'rule_id' => $ruleId,
                'code' => $this->fixture->getCouponCode(),
                'type' => $this->mappingData['coupon_type'][$this->fixture->getCouponType()],
                'usage_limit' => isset($this->data['uses_per_coupon'])
                    ? $this->data['uses_per_coupon']
                    : null,
                'usage_per_customer' => isset($this->data['usage_per_customer'])
                    ? $this->data['usage_per_customer']
                    : null,
                'is_primary' => true
            ])
        ];

        $this->webapiTransport->write($url, $data);
        $response = json_decode($this->webapiTransport->read(), true);
        $this->webapiTransport->close();

        if (empty($response['coupon_id'])) {
            $this->eventManager->dispatchEvent(['webapi_failed'], [$response]);
            throw new \Exception('Coupon creation by webapi handler was not successful!');
        }
    }

    /**
     * Convert condition data to webapi structure request.
     *
     * @param array $condition
     * @param string $prefix [optional]
     * @param int $indent [optional]
     * @return array
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function convertCondition(array $condition, $prefix = '', $indent = 1)
    {
        $result = [];

        $key = "{$prefix}{$indent}";
        $isContinue = isset($condition[$key]);
        while ($isContinue) {
            $childCondition = $this->convertCondition($condition, "{$prefix}{$indent}--", 1);
            $result[] = array_filter([
                'condition_type' => $condition[$key]['type'],
                'aggregator_type' => isset($condition[$key]['aggregator'])
                    ? $condition[$key]['aggregator']
                    : null,
                'attribute_name' => isset($condition[$key]['attribute'])
                    ? $condition[$key]['attribute']
                    : null,
                'operator' => isset($condition[$key]['operator'])
                    ? $condition[$key]['operator']
                    : null,
                'value' => $condition[$key]['value'],
                'conditions' => empty($childCondition) ? null : $childCondition
            ], [$this, 'filterCondition']);

            $indent += 1;
            $key = "{$prefix}{$indent}";
            $isContinue = isset($condition[$key]);
        }

        return $result;
    }

    /**
     * Prepare extension attributes for the sales rule.
     *
     * @return void
     */
    protected function prepareExtensionAttributes()
    {
        foreach ($this->data as $fieldName => $fieldValue) {
            if (!in_array($fieldName, $this->basicAttributes)) {
                $this->data['extension_attributes'][$fieldName] = $fieldValue;
                unset($this->data[$fieldName]);
            }
        }
    }

    /**
     * Filter condition data.
     *
     * @param mixed $var
     * @return bool
     */
    public function filterCondition($var)
    {
        return null !== $var;
    }
}
