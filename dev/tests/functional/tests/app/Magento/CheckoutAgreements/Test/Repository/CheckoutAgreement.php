<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
