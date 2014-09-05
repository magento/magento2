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

namespace Magento\Core\Test\TestCase;

use Magento\Core\Test\Fixture\SystemVariable;
use Magento\Core\Test\Page\Adminhtml\SystemVariableIndex;
use Magento\Core\Test\Page\Adminhtml\SystemVariableNew;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for DeleteCustomVariableEntityTest
 *
 * Test Flow:
 * Preconditions:
 * 1. Custom Variable is created
 *
 * Steps:
 * 1. Login to backend.
 * 2. Navigate to System->Other Settings->Custom Variables.
 * 3. Open Variable.
 * 4. Click 'Delete' button.
 * 5. Perform asserts.
 *
 * @group Variables_(PS)
 * @ZephyrId MAGETWO-25535
 */
class DeleteCustomVariableEntityTest extends Injectable
{
    /**
     * Custom System Variable grid page
     *
     * @var SystemVariableIndex
     */
    protected $systemVariableIndexPage;

    /**
     * Custom System Variable new and edit page
     *
     * @var SystemVariableNew
     */
    protected $systemVariableNewPage;

    /**
     * Injection data
     *
     * @param SystemVariableIndex $systemVariableIndex
     * @param SystemVariableNew $systemVariableNew
     * @return void
     */
    public function __inject(
        SystemVariableIndex $systemVariableIndex,
        SystemVariableNew $systemVariableNew
    ) {
        $this->systemVariableIndexPage = $systemVariableIndex;
        $this->systemVariableNewPage = $systemVariableNew;
    }

    /**
     * Delete Custom System Variable Entity test
     *
     * @param SystemVariable $systemVariable
     * @return void
     */
    public function test(SystemVariable $systemVariable)
    {
        // Precondition
        $systemVariable->persist();

        // Steps
        $filter = [
            'code' => $systemVariable->getCode(),
            'name' => $systemVariable->getName(),
        ];
        $this->systemVariableIndexPage->open();
        $this->systemVariableIndexPage->getSystemVariableGrid()->searchAndOpen($filter);
        $this->systemVariableNewPage->getFormPageActions()->delete();
    }
}
