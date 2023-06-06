<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Mftf\Helper;

use Exception;
use Magento\FunctionalTestingFramework\Helper\Helper;
use Magento\FunctionalTestingFramework\Module\MagentoWebDriver;

class AdminProductPage extends Helper
{
    /**
     * @param string $context
     * @param int $count
     */
    public function rapidChecksOnCheckBox(string $context, int $count)
    {
        try {
            /** @var MagentoWebDriver $webDriver */
            $webDriver = $this->getModule('\Magento\FunctionalTestingFramework\Module\MagentoWebDriver');
            for ($i = 0; $i < $count; $i++) {
                $webDriver->checkOption($context);
                $webDriver->waitForLoadingMaskToDisappear();
                $webDriver->waitForElementClickable($context);
                $webDriver->uncheckOption($context);
                $webDriver->waitForLoadingMaskToDisappear();
                $webDriver->waitForElementClickable($context);
            }
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
