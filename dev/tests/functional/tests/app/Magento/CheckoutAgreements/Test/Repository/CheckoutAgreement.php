<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\CheckoutAgreements\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class CheckoutAgreement
 * Checkout agreement repository
 */
class CheckoutAgreement extends AbstractRepository
{
    /**
     * @construct
     * @param array $defaultConfig
     * @param array $defaultData
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['term_disabled_text'] = [
            'name' => 'TermDisabledTextName%isolation%',
            'is_active' => 'Disabled',
            'is_html' => 'Text',
            'stores' => ['dataSet' => ['default']],
            'checkbox_text' => 'test_checkbox%isolation%',
            'content' => 'TestMessage%isolation%',
            'content_height' => '',
        ];

        $this->_data['term_disabled_html'] = [
            'name' => 'TermDisabledHtml%isolation%',
            'is_active' => 'Disabled',
            'is_html' => 'HTML',
            'stores' => ['dataSet' => ['default']],
            'checkbox_text' => 'test_checkbox%isolation%',
            'content' => 'TestMessage%isolation%',
            'content_height' => '',
        ];

        $this->_data['term_enabled_text'] = [
            'name' => 'TermEnabledTextName%isolation%',
            'is_active' => 'Enabled',
            'is_html' => 'Text',
            'stores' => ['dataSet' => ['default']],
            'checkbox_text' => 'test_checkbox%isolation%',
            'content' => 'TestMessage%isolation%',
            'content_height' => '',
        ];
    }
}
