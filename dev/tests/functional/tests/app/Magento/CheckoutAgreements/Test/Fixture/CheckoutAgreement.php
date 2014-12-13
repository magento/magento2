<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\CheckoutAgreements\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class CheckoutAgreement
 * Checkout agreement fixture.
 */
class CheckoutAgreement extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\CheckoutAgreements\Test\Repository\CheckoutAgreement';

    /**
     * @var string
     */
    // @codingStandardsIgnoreStart
    protected $handlerInterface = 'Magento\CheckoutAgreements\Test\Handler\CheckoutAgreement\CheckoutAgreementInterface';
    // @codingStandardsIgnoreEnd

    protected $defaultDataSet = [
        'name' => 'DefaultName%isolation%',
        'is_active' => 'Enabled',
        'is_html' => 'Text',
        'stores' => ['dataSet' => ['default']],
        'checkbox_text' => 'test_checkbox%isolation%',
        'content' => 'TestMessage%isolation%',
    ];

    protected $agreement_id = [
        'attribute_code' => 'agreement_id',
        'backend_type' => 'int',
        'is_required' => '1',
        'default_value' => '',
        'input' => '',
    ];

    protected $name = [
        'attribute_code' => 'name',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $content = [
        'attribute_code' => 'content',
        'backend_type' => 'text',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $content_height = [
        'attribute_code' => 'content_height',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $checkbox_text = [
        'attribute_code' => 'checkbox_text',
        'backend_type' => 'text',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $is_active = [
        'attribute_code' => 'is_active',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $is_html = [
        'attribute_code' => 'is_html',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $stores = [
        'attribute_code' => 'store_ids',
        'backend_type' => 'virtual',
        'source' => 'Magento\CheckoutAgreements\Test\Fixture\CheckoutAgreement\Stores',
    ];

    public function getAgreementId()
    {
        return $this->getData('agreement_id');
    }

    public function getName()
    {
        return $this->getData('name');
    }

    public function getContent()
    {
        return $this->getData('content');
    }

    public function getContentHeight()
    {
        return $this->getData('content_height');
    }

    public function getCheckboxText()
    {
        return $this->getData('checkbox_text');
    }

    public function getIsActive()
    {
        return $this->getData('is_active');
    }

    public function getIsHtml()
    {
        return $this->getData('is_html');
    }

    public function getStores()
    {
        return $this->getData('stores');
    }
}
