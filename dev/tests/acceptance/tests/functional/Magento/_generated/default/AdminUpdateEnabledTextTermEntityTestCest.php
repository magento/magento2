<?php
namespace Magento\AcceptanceTest\_default\Backend;

use Magento\FunctionalTestingFramework\AcceptanceTester;
use \Codeception\Util\Locator;
use Yandex\Allure\Adapter\Annotation\Features;
use Yandex\Allure\Adapter\Annotation\Stories;
use Yandex\Allure\Adapter\Annotation\Title;
use Yandex\Allure\Adapter\Annotation\Description;
use Yandex\Allure\Adapter\Annotation\Parameter;
use Yandex\Allure\Adapter\Annotation\Severity;
use Yandex\Allure\Adapter\Model\SeverityLevel;
use Yandex\Allure\Adapter\Annotation\TestCaseId;

/**
 * @Title("MC-14666: Update enabled Text checkout agreement")
 * @Description("Admin should be able to update enabled Text checkout agreement<h3 class='y-label y-label_status_broken'>Deprecated Notice(s):</h3><ul><li>DEPRECATED ACTION GROUP in Test: AddSimpleProductToCartActionGroup Avoid using super-ActionGroups. Use StorefrontOpenProductEntityPageActionGroup, StorefrontAddSimpleProductToCartActionGroup and StorefrontAssertProductAddedToCartResultMessageActionGroup</li></ul><h3>Test files</h3>app/code/Magento/CheckoutAgreements/Test/Mftf/Test/AdminUpdateEnabledTextTermEntityTest.xml<br>")
 * @TestCaseId("MC-14666")
 * @group checkoutAgreements
 * @group mtf_migrated
 */
class AdminUpdateEnabledTextTermEntityTestCest
{
	/**
	  * @param AcceptanceTester $I
	  * @throws \Exception
	  */
	public function _after(AcceptanceTester $I)
	{
		$setDisableTermsOnCheckout = $I->magentoCLI("config:set checkout/options/enable_agreements 0", 60); // stepKey: setDisableTermsOnCheckout
		$I->comment($setDisableTermsOnCheckout);
		$I->deleteEntity("createProduct", "hook"); // stepKey: deletedProduct
		$I->comment("Entering Action Group [openTermsGridToDelete] AdminTermsConditionsOpenGridActionGroup");
		$I->amOnPage("/" . getenv("MAGENTO_BACKEND_NAME") . "/checkout/agreement/"); // stepKey: onTermGridPageOpenTermsGridToDelete
		$I->waitForPageLoad(30); // stepKey: waitForPageLoadOpenTermsGridToDelete
		$I->comment("Exiting Action Group [openTermsGridToDelete] AdminTermsConditionsOpenGridActionGroup");
		$I->comment("Entering Action Group [openTermToDelete] AdminTermsConditionsEditTermByNameActionGroup");
		$I->fillField("#agreementGrid_filter_name", "name" . msq("disabledHtmlTerm")); // stepKey: fillTermNameFilterOpenTermToDelete
		$I->click("//div[contains(@class,'admin__data-grid-header')]//div[contains(@class,'admin__filter-actions')]/button[1]"); // stepKey: clickSearchButtonOpenTermToDelete
		$I->click(".data-grid>tbody>tr>td.col-id.col-agreement_id"); // stepKey: clickFirstRowOpenTermToDelete
		$I->waitForPageLoad(30); // stepKey: waitForEditTermPageLoadOpenTermToDelete
		$I->comment("Exiting Action Group [openTermToDelete] AdminTermsConditionsEditTermByNameActionGroup");
		$I->comment("Entering Action Group [deleteOpenedTerm] AdminTermsConditionsDeleteTermByNameActionGroup");
		$I->click(".page-main-actions #delete"); // stepKey: clickDeleteButtonDeleteOpenedTerm
		$I->waitForElementVisible("button.action-primary.action-accept", 30); // stepKey: waitForElementDeleteOpenedTerm
		$I->click("button.action-primary.action-accept"); // stepKey: clickDeleteOkButtonDeleteOpenedTerm
		$I->waitForText("You deleted the condition.", 30, ".message-success"); // stepKey: seeSuccessMessageDeleteOpenedTerm
		$I->comment("Exiting Action Group [deleteOpenedTerm] AdminTermsConditionsDeleteTermByNameActionGroup");
		$I->comment("Entering Action Group [adminLogout] AdminLogoutActionGroup");
		$I->amOnPage("/" . getenv("MAGENTO_BACKEND_NAME") . "/admin/auth/logout/"); // stepKey: amOnLogoutPageAdminLogout
		$I->comment("Exiting Action Group [adminLogout] AdminLogoutActionGroup");
	}

	/**
	  * @param AcceptanceTester $I
	  * @throws \Exception
	  */
	public function _failed(AcceptanceTester $I)
	{
		$I->saveScreenshot(); // stepKey: saveScreenshot
	}

	/**
	  * @param AcceptanceTester $I
	  * @throws \Exception
	  */
	public function _before(AcceptanceTester $I)
	{
		$setEnableTermsOnCheckout = $I->magentoCLI("config:set checkout/options/enable_agreements 1", 60); // stepKey: setEnableTermsOnCheckout
		$I->comment($setEnableTermsOnCheckout);
		$I->createEntity("createProduct", "hook", "SimpleTwo", [], []); // stepKey: createProduct
		$runCronIndex = $I->magentoCron("index", 90); // stepKey: runCronIndex
		$I->comment($runCronIndex);
		$I->comment("Entering Action Group [adminLogin] AdminLoginActionGroup");
		$I->amOnPage("/" . getenv("MAGENTO_BACKEND_NAME") . "/admin"); // stepKey: navigateToAdminAdminLogin
		$I->fillField("#username", getenv("MAGENTO_ADMIN_USERNAME")); // stepKey: fillUsernameAdminLogin
		$I->fillField("#login", getenv("MAGENTO_ADMIN_PASSWORD")); // stepKey: fillPasswordAdminLogin
		$I->click(".actions .action-primary"); // stepKey: clickLoginAdminLogin
		$I->waitForPageLoad(30); // stepKey: clickLoginAdminLoginWaitForPageLoad
		$I->conditionalClick(".modal-popup .action-secondary", ".modal-popup .action-secondary", true); // stepKey: clickDontAllowButtonIfVisibleAdminLogin
		$I->closeAdminNotification(); // stepKey: closeAdminNotificationAdminLogin
		$I->comment("Exiting Action Group [adminLogin] AdminLoginActionGroup");
	}

	/**
	 * @Features({"CheckoutAgreements"})
	 * @Stories({"Checkout agreements"})
	 * @Severity(level = SeverityLevel::CRITICAL)
	 * @Parameter(name = "AcceptanceTester", value="$I")
	 * @param AcceptanceTester $I
	 * @return void
	 * @throws \Exception
	 */
	public function AdminUpdateEnabledTextTermEntityTest(AcceptanceTester $I)
	{
		$I->comment("Entering Action Group [openNewTerm] AdminTermsConditionsOpenNewTermPageActionGroup");
		$I->amOnPage("/" . getenv("MAGENTO_BACKEND_NAME") . "/checkout/agreement/new"); // stepKey: amOnNewTermPageOpenNewTerm
		$I->waitForPageLoad(30); // stepKey: waitForAdminNewTermPageLoadOpenNewTerm
		$I->comment("Exiting Action Group [openNewTerm] AdminTermsConditionsOpenNewTermPageActionGroup");
		$I->comment("Entering Action Group [fillNewTerm] AdminTermsConditionsFillTermEditFormActionGroup");
		$I->fillField("#name", "name" . msq("activeTextTerm")); // stepKey: fillFieldConditionNameFillNewTerm
		$I->selectOption("#is_active", "Enabled"); // stepKey: selectOptionIsActiveFillNewTerm
		$I->selectOption("#is_html", "Text"); // stepKey: selectOptionIsHtmlFillNewTerm
		$I->selectOption("#mode", "Manually"); // stepKey: selectOptionModeFillNewTerm
		$I->selectOption("#stores", "Default Store View"); // stepKey: selectOptionStoreViewFillNewTerm
		$I->fillField("#checkbox_text", "test_checkbox" . msq("activeTextTerm")); // stepKey: fillFieldCheckboxTextFillNewTerm
		$I->fillField("#content", "TestMessage" . msq("activeTextTerm")); // stepKey: fillFieldContentFillNewTerm
		$I->comment("Exiting Action Group [fillNewTerm] AdminTermsConditionsFillTermEditFormActionGroup");
		$I->comment("Entering Action Group [saveNewTerm] AdminTermsConditionsSaveTermActionGroup");
		$I->click(".page-main-actions #save"); // stepKey: saveTermSaveNewTerm
		$I->see("You saved the condition.", ".message-success"); // stepKey: seeSuccessMessageSaveNewTerm
		$I->comment("Exiting Action Group [saveNewTerm] AdminTermsConditionsSaveTermActionGroup");
		$I->comment("Entering Action Group [openTermsGrid] AdminTermsConditionsOpenGridActionGroup");
		$I->amOnPage("/" . getenv("MAGENTO_BACKEND_NAME") . "/checkout/agreement/"); // stepKey: onTermGridPageOpenTermsGrid
		$I->waitForPageLoad(30); // stepKey: waitForPageLoadOpenTermsGrid
		$I->comment("Exiting Action Group [openTermsGrid] AdminTermsConditionsOpenGridActionGroup");
		$I->comment("Entering Action Group [openUpdateTerm] AdminTermsConditionsEditTermByNameActionGroup");
		$I->fillField("#agreementGrid_filter_name", "name" . msq("activeTextTerm")); // stepKey: fillTermNameFilterOpenUpdateTerm
		$I->click("//div[contains(@class,'admin__data-grid-header')]//div[contains(@class,'admin__filter-actions')]/button[1]"); // stepKey: clickSearchButtonOpenUpdateTerm
		$I->click(".data-grid>tbody>tr>td.col-id.col-agreement_id"); // stepKey: clickFirstRowOpenUpdateTerm
		$I->waitForPageLoad(30); // stepKey: waitForEditTermPageLoadOpenUpdateTerm
		$I->comment("Exiting Action Group [openUpdateTerm] AdminTermsConditionsEditTermByNameActionGroup");
		$I->comment("Entering Action Group [fillUpdateTerm] AdminTermsConditionsFillTermEditFormActionGroup");
		$I->fillField("#name", "name" . msq("disabledHtmlTerm")); // stepKey: fillFieldConditionNameFillUpdateTerm
		$I->selectOption("#is_active", "Disabled"); // stepKey: selectOptionIsActiveFillUpdateTerm
		$I->selectOption("#is_html", "HTML"); // stepKey: selectOptionIsHtmlFillUpdateTerm
		$I->selectOption("#mode", "Manually"); // stepKey: selectOptionModeFillUpdateTerm
		$I->selectOption("#stores", "Default Store View"); // stepKey: selectOptionStoreViewFillUpdateTerm
		$I->fillField("#checkbox_text", "test_checkbox" . msq("disabledHtmlTerm")); // stepKey: fillFieldCheckboxTextFillUpdateTerm
		$I->fillField("#content", "<html>"); // stepKey: fillFieldContentFillUpdateTerm
		$I->comment("Exiting Action Group [fillUpdateTerm] AdminTermsConditionsFillTermEditFormActionGroup");
		$I->comment("Entering Action Group [saveUpdateTerm] AdminTermsConditionsSaveTermActionGroup");
		$I->click(".page-main-actions #save"); // stepKey: saveTermSaveUpdateTerm
		$I->see("You saved the condition.", ".message-success"); // stepKey: seeSuccessMessageSaveUpdateTerm
		$I->comment("Exiting Action Group [saveUpdateTerm] AdminTermsConditionsSaveTermActionGroup");
		$I->comment("Entering Action Group [openNewTermsGrid] AdminTermsConditionsOpenGridActionGroup");
		$I->amOnPage("/" . getenv("MAGENTO_BACKEND_NAME") . "/checkout/agreement/"); // stepKey: onTermGridPageOpenNewTermsGrid
		$I->waitForPageLoad(30); // stepKey: waitForPageLoadOpenNewTermsGrid
		$I->comment("Exiting Action Group [openNewTermsGrid] AdminTermsConditionsOpenGridActionGroup");
		$I->comment("Entering Action Group [assertTermInGrid] AssertAdminTermsConditionsInGridActionGroup");
		$I->fillField("#agreementGrid_filter_name", "name" . msq("disabledHtmlTerm")); // stepKey: fillTermNameFilterAssertTermInGrid
		$I->click("//div[contains(@class,'admin__data-grid-header')]//div[contains(@class,'admin__filter-actions')]/button[1]"); // stepKey: clickSearchButtonAssertTermInGrid
		$I->see("name" . msq("disabledHtmlTerm"), ".data-grid>tbody>tr>td.col-name"); // stepKey: assertTermInGridAssertTermInGrid
		$I->comment("Exiting Action Group [assertTermInGrid] AssertAdminTermsConditionsInGridActionGroup");
		$I->openNewTab(); // stepKey: openStorefrontTab
		$I->comment("Entering Action Group [addProductToTheCart] AddSimpleProductToCartActionGroup");
		$I->amOnPage("/" . $I->retrieveEntityField('createProduct', 'custom_attributes[url_key]', 'test') . ".html"); // stepKey: goToProductPageAddProductToTheCart
		$I->waitForPageLoad(30); // stepKey: waitForProductPageAddProductToTheCart
		$I->click("button.action.tocart.primary"); // stepKey: addToCartAddProductToTheCart
		$I->waitForPageLoad(30); // stepKey: addToCartAddProductToTheCartWaitForPageLoad
		$I->waitForElementNotVisible("//button/span[text()='Adding...']", 30); // stepKey: waitForElementNotVisibleAddToCartButtonTitleIsAddingAddProductToTheCart
		$I->waitForElementNotVisible("//button/span[text()='Added']", 30); // stepKey: waitForElementNotVisibleAddToCartButtonTitleIsAddedAddProductToTheCart
		$I->waitForElementVisible("//button/span[text()='Add to Cart']", 30); // stepKey: waitForElementVisibleAddToCartButtonTitleIsAddToCartAddProductToTheCart
		$I->waitForPageLoad(30); // stepKey: waitForPageLoadAddProductToTheCart
		$I->waitForElementVisible("div.message-success.success.message", 30); // stepKey: waitForProductAddedMessageAddProductToTheCart
		$I->see("You added " . $I->retrieveEntityField('createProduct', 'name', 'test') . " to your shopping cart.", "div.message-success.success.message"); // stepKey: seeAddToCartSuccessMessageAddProductToTheCart
		$I->comment("Exiting Action Group [addProductToTheCart] AddSimpleProductToCartActionGroup");
		$I->comment("Entering Action Group [processCheckoutToThePaymentMethodsPage] StorefrontProcessCheckoutToPaymentActionGroup");
		$I->comment("Go to Checkout");
		$I->waitForElementNotVisible(".counter.qty.empty", 30); // stepKey: waitUpdateQuantityProcessCheckoutToThePaymentMethodsPage
		$I->wait(5); // stepKey: waitMinicartRenderingProcessCheckoutToThePaymentMethodsPage
		$I->click("a.showcart"); // stepKey: clickCartProcessCheckoutToThePaymentMethodsPage
		$I->waitForPageLoad(60); // stepKey: clickCartProcessCheckoutToThePaymentMethodsPageWaitForPageLoad
		$I->click("#top-cart-btn-checkout"); // stepKey: goToCheckoutProcessCheckoutToThePaymentMethodsPage
		$I->waitForPageLoad(30); // stepKey: goToCheckoutProcessCheckoutToThePaymentMethodsPageWaitForPageLoad
		$I->comment("Process steps");
		$I->fillField("input[id*=customer-email]", msq("CustomerEntityOne") . "test@email.com"); // stepKey: enterEmailProcessCheckoutToThePaymentMethodsPage
		$I->fillField("input[name=firstname]", "John"); // stepKey: enterFirstNameProcessCheckoutToThePaymentMethodsPage
		$I->fillField("input[name=lastname]", "Doe"); // stepKey: enterLastNameProcessCheckoutToThePaymentMethodsPage
		$I->fillField("input[name='street[0]']", "7700 W Parmer Ln"); // stepKey: enterStreetProcessCheckoutToThePaymentMethodsPage
		$I->fillField("input[name=city]", "Austin"); // stepKey: enterCityProcessCheckoutToThePaymentMethodsPage
		$I->selectOption("select[name=region_id]", "Texas"); // stepKey: selectRegionProcessCheckoutToThePaymentMethodsPage
		$I->fillField("input[name=postcode]", "78729"); // stepKey: enterPostcodeProcessCheckoutToThePaymentMethodsPage
		$I->fillField("input[name=telephone]", "1234568910"); // stepKey: enterTelephoneProcessCheckoutToThePaymentMethodsPage
		$I->waitForLoadingMaskToDisappear(); // stepKey: waitForShippingMethodsProcessCheckoutToThePaymentMethodsPage
		$I->click("//div[@id='checkout-shipping-method-load']//td[contains(., '')]/..//input"); // stepKey: selectShippingMethodProcessCheckoutToThePaymentMethodsPage
		$I->waitForElement("button.button.action.continue.primary", 30); // stepKey: waitForTheNextButtonProcessCheckoutToThePaymentMethodsPage
		$I->waitForPageLoad(30); // stepKey: waitForTheNextButtonProcessCheckoutToThePaymentMethodsPageWaitForPageLoad
		$I->waitForElementNotVisible(".loading-mask", 300); // stepKey: waitForProcessShippingMethodProcessCheckoutToThePaymentMethodsPage
		$I->click("button.button.action.continue.primary"); // stepKey: clickNextProcessCheckoutToThePaymentMethodsPage
		$I->waitForPageLoad(30); // stepKey: clickNextProcessCheckoutToThePaymentMethodsPageWaitForPageLoad
		$I->waitForElement("//*[@id='checkout-payment-method-load']//div[@data-role='title']", 30); // stepKey: waitForPaymentSectionLoadedProcessCheckoutToThePaymentMethodsPage
		$I->seeInCurrentUrl("/checkout/#payment"); // stepKey: assertCheckoutPaymentUrlProcessCheckoutToThePaymentMethodsPage
		$I->comment("Exiting Action Group [processCheckoutToThePaymentMethodsPage] StorefrontProcessCheckoutToPaymentActionGroup");
		$I->comment("Entering Action Group [assertTermInCheckout] AssertStorefrontTermAbsentInCheckoutActionGroup");
		$I->comment("Check if agreement is absent on checkout");
		$I->dontSee("test_checkbox" . msq("disabledHtmlTerm"), "div.checkout-agreements-block > div > div > div > label > button > span"); // stepKey: seeTermInCheckoutAssertTermInCheckout
		$I->comment("Checkout select Check/Money Order payment");
		$I->waitForLoadingMaskToDisappear(); // stepKey: waitForPaymentPageRenderingAssertTermInCheckout
		$I->waitForPageLoad(30); // stepKey: waitForPaymentRenderingAssertTermInCheckout
		$I->conditionalClick("//div[@id='checkout-payment-method-load']//div[@class='payment-method']//label//span[contains(., 'Check / Money order')]/../..//input", "//div[@id='checkout-payment-method-load']//div[@class='payment-method']//label//span[contains(., 'Check / Money order')]/../..//input", true); // stepKey: selectCheckmoPaymentMethodAssertTermInCheckout
		$I->waitForLoadingMaskToDisappear(); // stepKey: waitForLoadingMaskAfterPaymentMethodSelectionAssertTermInCheckout
		$I->comment("Click Place Order button");
		$I->click(".payment-method._active button.action.primary.checkout"); // stepKey: clickPlaceOrderAssertTermInCheckout
		$I->waitForPageLoad(30); // stepKey: clickPlaceOrderAssertTermInCheckoutWaitForPageLoad
		$I->comment("See success messages");
		$I->see("Thank you for your purchase!", ".page-title"); // stepKey: seeSuccessTitleAssertTermInCheckout
		$I->see("Your order # is: ", ".checkout-success > p:nth-child(1)"); // stepKey: seeOrderNumberAssertTermInCheckout
		$I->comment("Exiting Action Group [assertTermInCheckout] AssertStorefrontTermAbsentInCheckoutActionGroup");
		$I->closeTab(); // stepKey: closeStorefrontTab
	}
}
