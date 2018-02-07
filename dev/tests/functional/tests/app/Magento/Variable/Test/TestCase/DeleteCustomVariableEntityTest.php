<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Variable\Test\TestCase;

use Magento\Variable\Test\Fixture\SystemVariable;
use Magento\Variable\Test\Page\Adminhtml\SystemVariableIndex;
use Magento\Variable\Test\Page\Adminhtml\SystemVariableNew;
use Magento\Mtf\TestCase\Injectable;

/**
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
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'PS';
    /* end tags */

    /**
     * Custom System Variable grid page.
     *
     * @var SystemVariableIndex
     */
    protected $systemVariableIndexPage;

    /**
     * Custom System Variable new and edit page.
     *
     * @var SystemVariableNew
     */
    protected $systemVariableNewPage;

    /**
     * Injection data.
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
     * Delete Custom System Variable Entity test.
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
        $this->systemVariableNewPage->getModalBlock()->acceptAlert();
    }
}
