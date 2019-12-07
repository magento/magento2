<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Variable\Test\TestCase;

use Magento\Variable\Test\Fixture\SystemVariable;
use Magento\Variable\Test\Page\Adminhtml\SystemVariableIndex;
use Magento\Variable\Test\Page\Adminhtml\SystemVariableNew;
use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Preconditions:
 * 1. Custom system variable is created.
 * 2. Additional Non Default Storeview is created.
 *
 * Steps:
 * 1. Login to backend.
 * 2. Navigate to System->Other Settings->Custom Variables.
 * 3. Open from grid created custom system variable.
 * 4. Navigate to the Store Switcher.
 * 5. Choose Appropriate Storeview (non default).
 * 6. Set Use Default Variable Values.
 * 7. Edit necessary fields.
 * 8. Save Custom variable using correspond saveActions.
 * 9. Perform all assertions.
 *
 * @group Variables
 * @ZephyrId MAGETWO-26104
 */
class UpdateCustomVariableEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
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
     * Store entity.
     *
     * @var Store
     */
    protected $store = null;

    /**
     * Configuration setting.
     *
     * @var string
     */
    private $configData;

    /**
     * Factory for Test Steps.
     *
     * @var TestStepFactory
     */
    private $testStepFactory;

    /**
     * Injection data.
     *
     * @param SystemVariableIndex $systemVariableIndex
     * @param SystemVariableNew $systemVariableNew
     * @param TestStepFactory $testStepFactory
     * @return void
     */
    public function __inject(
        SystemVariableIndex $systemVariableIndex,
        SystemVariableNew $systemVariableNew,
        TestStepFactory $testStepFactory
    ) {
        $this->systemVariableIndexPage = $systemVariableIndex;
        $this->systemVariableNewPage = $systemVariableNew;
        $this->testStepFactory = $testStepFactory;
    }

    /**
     * Update Custom System Variable Entity test.
     *
     * @param FixtureFactory $fixtureFactory
     * @param SystemVariable $customVariable
     * @param SystemVariable $customVariableOrigin
     * @param string $saveAction
     * @param string|null $configData [optional]
     * @return array
     */
    public function test(
        FixtureFactory $fixtureFactory,
        SystemVariable $customVariable,
        SystemVariable $customVariableOrigin,
        $saveAction,
        $configData = null
    ) {
        // Preconditions
        $this->configData = $configData;
        $this->testStepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $this->store = $fixtureFactory->createByCode('store', ['dataset' => 'custom']);
        $this->store->persist();
        $customVariableOrigin->persist();
        $filter = ['code' => $customVariableOrigin->getCode()];

        // Steps
        $this->systemVariableIndexPage->open();
        $this->systemVariableIndexPage->getSystemVariableGrid()->searchAndOpen($filter);
        $this->systemVariableNewPage->getFormPageActions()->selectStoreView($this->store->getData('name'));
        $this->systemVariableNewPage->getSystemVariableForm()->fill($customVariable);
        $this->systemVariableNewPage->getFormPageActions()->$saveAction();
        return [
            'storeOrigin' => $this->store,
            'customVariableOrigin' => $customVariableOrigin
        ];
    }

    /**
     * Delete Store after test.
     *
     * @return void
     */
    public function tearDown()
    {
        if ($this->store !== null) {
            $storeIndex = $this->objectManager->create(\Magento\Backend\Test\Page\Adminhtml\StoreIndex::class);
            $storeIndex->open();
            $storeIndex->getStoreGrid()->searchAndOpen(['store_title' => $this->store->getName()]);
            $storeNew = $this->objectManager->create(\Magento\Backend\Test\Page\Adminhtml\StoreNew::class);
            $storeNew->getFormPageActions()->delete();
            $storeDelete = $this->objectManager->create(\Magento\Backend\Test\Page\Adminhtml\StoreDelete::class);
            $storeDelete->getStoreForm()->fillForm(['create_backup' => 'No']);
            $storeDelete->getFormPageActions()->delete();
        }
        $this->store = null;
    }
}
