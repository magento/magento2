<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Block\Adminhtml\Config\Form\Field;

use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test for \Magento\Reports\Block\Adminhtml\Config\Form\Field\YtdStart.
 *
 * @magentoAppArea adminhtml
 */
class YtdStartTest extends AbstractBackendController
{
    /**
     * @var array
     */
    private $monthNumbers = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];

    /**
     * Test Get Month and Day Element renderer
     *
     * @return void
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testGetElementHtml(): void
    {
        $this->dispatch('backend/admin/system_config/edit/section/reports/');
        $body = $this->getResponse()->getBody();

        $this->assertOptionSelected('01', $body);
    }

    /**
     * Assert that given option is selected.
     *
     * @param string $option Option value.
     * @param string $content HTML content
     * @return void
     */
    private function assertOptionSelected(string $option, string $content): void
    {
        foreach ($this->monthNumbers as $monthNumber) {
            $regEx = "\<option[^\>]+value\=\\\"$monthNumber\\\"[^\>]*?";
            if ($monthNumber ===  $option) {
                $regEx .= 'selected\=\"selected\"[^\>]*?';
            }
            $regEx .= "\>$monthNumber\<\/option\>";
            $this->assertMatchesRegularExpression("#$regEx#", $content);
        }
    }
}
