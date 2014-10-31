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

namespace Magento\GiftMessage\Test\TestCase;

use Mtf\TestCase\Scenario;

/**
 * Test Creation for CreateGiftMessageOnBackend
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create Product according dataSet.
 * 2. Enable Gift Messages (Order/Items level).
 *
 * Steps:
 * 1. Login to backend
 * 2. Go to Sales >Orders
 * 3. Create new order
 * 4. Fill data form dataSet
 * 5. Perform all asserts
 *
 * @group Gift_Messages_(CS)
 * @ZephyrId MAGETWO-29642
 */
class CreateGiftMessageOnBackendTest extends Scenario
{
    /**
     * Run CreateGiftMessageOnBackend test.
     *
     * @return void
     */
    public function test()
    {
        $this->executeScenario();
    }

    /**
     * Disable enabled config after test.
     *
     * @return void
     */
    public function tearDown()
    {
        $setConfigStep = $this->objectManager->create(
            'Magento\Core\Test\TestStep\SetupConfigurationStep',
            ['configData' => 'cashondelivery', 'rollback' => true]
        );
        $setConfigStep->run();
    }
}
