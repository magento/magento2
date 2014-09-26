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

use Mtf\ObjectManager;
use Mtf\TestCase\Injectable;
use Magento\Store\Test\Fixture\Store;
use Magento\Core\Test\Fixture\SystemVariable;
use Magento\Core\Test\Page\Adminhtml\SystemVariableNew;
use Magento\Core\Test\Page\Adminhtml\SystemVariableIndex;

/**
 * Test Creation for UpdateCustomVariableEntityTest
 *
 * Test Flow:
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
 * @group Variables_(PS)
 * @ZephyrId MAGETWO-26104
 */
class UpdateCustomVariableEntityTest extends Injectable
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
     * Store Name
     *
     * @var array
     */
    public static $storeName;

    /**
     * Prepare data
     *
     * @param Store $storeOrigin
     * @return array
     */
    public function __prepare(Store $storeOrigin)
    {
        $storeOrigin->persist();
        self::$storeName = $storeOrigin->getName();

        return ['storeOrigin' => $storeOrigin];
    }

    /**
     * Injection data
     *
     * @param SystemVariableIndex $systemVariableIndex
     * @param SystemVariableNew $systemVariableNew
     * @param SystemVariable $customVariableOrigin
     * @return array
     */
    public function __inject(
        SystemVariableIndex $systemVariableIndex,
        SystemVariableNew $systemVariableNew,
        SystemVariable $customVariableOrigin
    ) {
        $this->systemVariableIndexPage = $systemVariableIndex;
        $this->systemVariableNewPage = $systemVariableNew;

        $customVariableOrigin->persist();

        return ['customVariableOrigin' => $customVariableOrigin];
    }

    /**
     * Update Custom System Variable Entity test
     *
     * @param SystemVariable $customVariable
     * @param SystemVariable $customVariableOrigin
     * @param Store $storeOrigin
     * @param $saveAction
     * @return void
     */
    public function test(
        SystemVariable $customVariable,
        SystemVariable $customVariableOrigin,
        Store $storeOrigin,
        $saveAction
    ) {
        $filter = [
            'code' => $customVariableOrigin->getCode(),
        ];

        // Steps
        $this->systemVariableIndexPage->open();
        $this->systemVariableIndexPage->getSystemVariableGrid()->searchAndOpen($filter);
        $this->systemVariableNewPage->getFormPageActions()->selectStoreView($storeOrigin->getData('name'));
        $this->systemVariableNewPage->getSystemVariableForm()->fill($customVariable);
        $this->systemVariableNewPage->getFormPageActions()->$saveAction();
    }

    /**
     * Delete Store after test
     *
     * @return void
     */
    public static function tearDownAfterClass()
    {
        $filter['store_title'] = self::$storeName;
        $storeIndex = ObjectManager::getInstance()->create('Magento\Backend\Test\Page\Adminhtml\StoreIndex');
        $storeIndex->open();
        $storeIndex->getStoreGrid()->searchAndOpen($filter);
        $storeNew = ObjectManager::getInstance()->create('Magento\Backend\Test\Page\Adminhtml\StoreNew');
        $storeNew->getFormPageActions()->delete();
        $storeDelete = ObjectManager::getInstance()->create('Magento\Backend\Test\Page\Adminhtml\StoreDelete');
        $storeDelete->getStoreForm()->fillForm(['create_backup' => 'No']);
        $storeDelete->getFormPageFooterActions()->delete();
    }
}
