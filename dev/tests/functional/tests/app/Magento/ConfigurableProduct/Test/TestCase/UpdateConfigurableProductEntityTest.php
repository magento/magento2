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

namespace Magento\ConfigurableProduct\Test\TestCase;

use Mtf\TestCase\Scenario;

/**
 * Test Flow:
 *
 * Preconditions:
 * 1. Two simple products are created.
 * 2. Configurable attribute with two options is created.
 * 3. Configurable attribute added to default template.
 * 4. Configurable product is created.
 *
 * Steps:
 * 1. Log in to backend.
 * 2. Open Products -> Catalog.
 * 3. Search and open configurable product from preconditions.
 * 4. Fill in data according to dataSet.
 * 5. Save product.
 * 6. Perform all assertions.
 *
 * @group Configurable_Product_(MX)
 * @ZephyrId MAGETWO-29916
 */
class UpdateConfigurableProductEntityTest extends Scenario
{
    /**
     * Update configurable product.
     *
     * @return array
     */
    public function test()
    {
        $this->executeScenario();
    }
}
