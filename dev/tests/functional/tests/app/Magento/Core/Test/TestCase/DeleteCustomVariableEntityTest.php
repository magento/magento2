<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
