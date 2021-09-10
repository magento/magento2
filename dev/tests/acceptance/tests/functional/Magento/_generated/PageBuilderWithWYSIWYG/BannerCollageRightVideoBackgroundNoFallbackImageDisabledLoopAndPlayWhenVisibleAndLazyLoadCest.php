<?php
namespace Magento\AcceptanceTest\_PageBuilderWithWYSIWYG\Backend;

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
 * @Title("MC-31667: Validate Banner with Collage Right Appearance with No Fallback Image, Disabled Infinite Loop, Disabled Play Only When Visible, and Disabled Lazy Load")
 * @Description("Validate video background with no fallback image, disabled infinite loop, disabled play only when visible, and disabled lazy load.<h3>Test files</h3>magento2-page-builder/app/code/Magento/PageBuilder/Test/Mftf/Test/AdminPageBuilderBannerCollageRightAppearanceTest/BannerCollageRightVideoBackgroundNoFallbackImageDisabledLoopAndPlayWhenVisibleAndLazyLoad.xml<br>")
 * @TestCaseId("MC-31667")
 * @group pagebuilder
 * @group pagebuilder-banner
 * @group pagebuilder-bannerCollageRight
 * @group pagebuilder-backgroundForm
 * @group pagebuilder-video-background
 */
class BannerCollageRightVideoBackgroundNoFallbackImageDisabledLoopAndPlayWhenVisibleAndLazyLoadCest
{
	/**
	  * @param AcceptanceTester $I
	  * @throws \Exception
	  */
	public function _before(AcceptanceTester $I)
	{
		$I->createEntity("createCMSPage", "hook", "_emptyCmsPage", [], []); // stepKey: createCMSPage
		$I->comment("Entering Action Group [loginAsAdmin] AdminLoginActionGroup");
		$I->amOnPage((getenv("MAGENTO_BACKEND_BASE_URL") ? rtrim(getenv("MAGENTO_BACKEND_BASE_URL"), "/") : "") . "/" . getenv("MAGENTO_BACKEND_NAME") . "/admin"); // stepKey: navigateToAdminLoginAsAdmin
		$I->fillField("#username", getenv("MAGENTO_ADMIN_USERNAME")); // stepKey: fillUsernameLoginAsAdmin
		$I->fillField("#login", getenv("MAGENTO_ADMIN_PASSWORD")); // stepKey: fillPasswordLoginAsAdmin
		$I->click(".actions .action-primary"); // stepKey: clickLoginLoginAsAdmin
		$I->waitForPageLoad(30); // stepKey: clickLoginLoginAsAdminWaitForPageLoad
		$I->conditionalClick(".modal-popup .action-secondary", ".modal-popup .action-secondary", true); // stepKey: clickDontAllowButtonIfVisibleLoginAsAdmin
		$I->closeAdminNotification(); // stepKey: closeAdminNotificationLoginAsAdmin
		$I->comment("Exiting Action Group [loginAsAdmin] AdminLoginActionGroup");
		$I->comment("Entering Action Group [navigateToCreatedCMSPage] NavigateToCreatedCMSPageActionGroup");
		$I->amOnPage((getenv("MAGENTO_BACKEND_BASE_URL") ? rtrim(getenv("MAGENTO_BACKEND_BASE_URL"), "/") : "") . "/" . getenv("MAGENTO_BACKEND_NAME") . "/cms/page"); // stepKey: navigateToCMSPagesGridNavigateToCreatedCMSPage
		$I->waitForPageLoad(30); // stepKey: waitForPageLoad1NavigateToCreatedCMSPage
		$I->conditionalClick("//div[@class='admin__data-grid-header']//button[contains(text(), 'Clear all')]", "//div[@class='admin__data-grid-header']//span[contains(text(), 'Active filters:')]", true); // stepKey: clickToResetFilterNavigateToCreatedCMSPage
		$I->waitForPageLoad(30); // stepKey: waitForPageLoad2NavigateToCreatedCMSPage
		$I->conditionalClick("//div[contains(@data-role, 'grid-wrapper')]/table/thead/tr/th/span[contains(text(), 'ID')]", "//span[contains(text(), 'ID')]/parent::th[not(contains(@class, '_descend'))]/parent::tr/parent::thead/parent::table/parent::div[contains(@data-role, 'grid-wrapper')]", true); // stepKey: clickToAttemptSortByIdDescendingNavigateToCreatedCMSPage
		$I->waitForLoadingMaskToDisappear(); // stepKey: waitForFirstIdSortDescendingToFinishNavigateToCreatedCMSPage
		$I->comment("Conditional Click again in case it goes from default state to ascending on first click");
		$I->conditionalClick("//div[contains(@data-role, 'grid-wrapper')]/table/thead/tr/th/span[contains(text(), 'ID')]", "//span[contains(text(), 'ID')]/parent::th[not(contains(@class, '_descend'))]/parent::tr/parent::thead/parent::table/parent::div[contains(@data-role, 'grid-wrapper')]", true); // stepKey: secondClickToAttemptSortByIdDescendingNavigateToCreatedCMSPage
		$I->waitForLoadingMaskToDisappear(); // stepKey: waitForSecondIdSortDescendingToFinishNavigateToCreatedCMSPage
		$I->click("//div[text()='" . $I->retrieveEntityField('createCMSPage', 'identifier', 'hook') . "']/parent::td//following-sibling::td[@class='data-grid-actions-cell']//button[text()='Select']"); // stepKey: clickSelectCreatedCMSPageNavigateToCreatedCMSPage
		$I->click("//div[text()='" . $I->retrieveEntityField('createCMSPage', 'identifier', 'hook') . "']/parent::td//following-sibling::td[@class='data-grid-actions-cell']//a[text()='Edit']"); // stepKey: navigateToCreatedCMSPageNavigateToCreatedCMSPage
		$I->waitForPageLoad(30); // stepKey: waitForPageLoad3NavigateToCreatedCMSPage
		$I->click("div[data-index=content]"); // stepKey: clickExpandContentTabForPageNavigateToCreatedCMSPage
		$I->waitForLoadingMaskToDisappear(); // stepKey: waitForLoadingMaskOfStagingSectionNavigateToCreatedCMSPage
		$I->comment("Exiting Action Group [navigateToCreatedCMSPage] NavigateToCreatedCMSPageActionGroup");
		$I->comment("Entering Action Group [switchToPageBuilderStage] switchToPageBuilderStage");
		$I->waitForElementVisible("div[data-index=content]", 30); // stepKey: waitForSectionSwitchToPageBuilderStage
		$I->conditionalClick("div[data-index=content]", "div[data-index=content]._show", false); // stepKey: expandSectionSwitchToPageBuilderStage
		$I->waitForPageLoad(30); // stepKey: waitForStageToLoadSwitchToPageBuilderStage
		$I->waitForElementVisible("div.stage-content-snapshot", 30); // stepKey: waitForSnapshotSwitchToPageBuilderStage
		$I->waitForElementVisible("//button/span[contains(text(), 'Edit with Page Builder')]", 30); // stepKey: waitForEditButtonSwitchToPageBuilderStage
		$I->click("//button/span[contains(text(), 'Edit with Page Builder')]"); // stepKey: clickEditButtonSwitchToPageBuilderStage
		$I->waitForPageLoad(30); // stepKey: waitForFullScreenAnimationSwitchToPageBuilderStage
		$I->waitForElementNotVisible("div.pagebuilder-stage-loading", 30); // stepKey: waitForStageLoadingGraphicNotVisibleSwitchToPageBuilderStage
		$I->waitForElementVisible("(//div[contains(@class,\"pagebuilder-content-type\") and contains(@class,\"pagebuilder-root-container\")])[1]", 30); // stepKey: waitForPageBuilderRootContainerSwitchToPageBuilderStage
		$I->comment("removing deprecated element");
		$I->comment("Exiting Action Group [switchToPageBuilderStage] switchToPageBuilderStage");
		$I->comment("Entering Action Group [dragRowToRootContainer] dragContentTypeToStage");
		$I->waitForElementVisible("//div[contains(@class,'stage-is-active')]//*[text()=\"Row\"]/ancestor::*[contains(@class, \"ui-draggable\")]", 30); // stepKey: waitForContentTypeInPanelDragRowToRootContainer
		$I->dragAndDrop("//div[contains(@class,'stage-is-active')]//*[text()=\"Row\"]/ancestor::*[contains(@class, \"ui-draggable\")]", "(//div[contains(@class, \"pagebuilder-content-type\") and contains(concat(' ', @class, ' '), ' pagebuilder-root-container ')])[1]/descendant::div[contains(@class, \"element-children\")]"); // stepKey: dropContentTypeIntoStageDragRowToRootContainer
		$I->waitForPageLoad(30); // stepKey: waitForAnimationDragRowToRootContainer
		$I->dontSeeJsError(); // stepKey: doNotSeeJSErrorInConsoleDragRowToRootContainer
		$I->waitForElementVisible("//div[contains(@class, \"pagebuilder-content-type\") and contains(concat(' ', @class, ' '), ' pagebuilder-row ')]", 10); // stepKey: waitForContentTypeInStageDragRowToRootContainer
		$I->comment("Exiting Action Group [dragRowToRootContainer] dragContentTypeToStage");
	}

	/**
	  * @param AcceptanceTester $I
	  * @throws \Exception
	  */
	public function _after(AcceptanceTester $I)
	{
		$I->deleteEntity("createCMSPage", "hook"); // stepKey: deleteCMSPage
		$I->comment("Entering Action Group [logout] AdminLogoutActionGroup");
		$I->amOnPage((getenv("MAGENTO_BACKEND_BASE_URL") ? rtrim(getenv("MAGENTO_BACKEND_BASE_URL"), "/") : "") . "/" . getenv("MAGENTO_BACKEND_NAME") . "/admin/auth/logout/"); // stepKey: amOnLogoutPageLogout
		$I->comment("Exiting Action Group [logout] AdminLogoutActionGroup");
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
	 * @Features({"PageBuilder"})
	 * @Stories({"Banner"})
	 * @Severity(level = SeverityLevel::BLOCKER)
	 * @Parameter(name = "AcceptanceTester", value="$I")
	 * @param AcceptanceTester $I
	 * @return void
	 * @throws \Exception
	 */
	public function BannerCollageRightVideoBackgroundNoFallbackImageDisabledLoopAndPlayWhenVisibleAndLazyLoad(AcceptanceTester $I)
	{
		$I->comment("Entering Action Group [expandPageBuilderPanelMenuSection] expandPageBuilderPanelMenuSection");
		$I->waitForElementVisible("//div[contains(@class,'stage-is-active')]//div[@id='pagebuilder-panel']//h4[.='Media']", 30); // stepKey: waitForcontentTypeVisibleExpandPageBuilderPanelMenuSection
		$I->conditionalClick("//div[contains(@class,'stage-is-active')]//div[@id='pagebuilder-panel']//h4[.='Media']", "//div[contains(@class,'stage-is-active')]//div[@id='pagebuilder-panel']//li[@class='active']//h4[.='Media']", false); // stepKey: expandContentTypeMenuSectionExpandPageBuilderPanelMenuSection
		$I->waitForElementVisible("//div[contains(@class,'stage-is-active')]//div[@id='pagebuilder-panel']//li[@class='active']//h4[.='Media']", 30); // stepKey: waitForMenuSectionExpandedExpandPageBuilderPanelMenuSection
		$I->comment("Exiting Action Group [expandPageBuilderPanelMenuSection] expandPageBuilderPanelMenuSection");
		$I->comment("Entering Action Group [dragBannerIntoStage] dragContentTypeToStage");
		$I->waitForElementVisible("//div[contains(@class,'stage-is-active')]//*[text()=\"Banner\"]/ancestor::*[contains(@class, \"ui-draggable\")]", 30); // stepKey: waitForContentTypeInPanelDragBannerIntoStage
		$I->dragAndDrop("//div[contains(@class,'stage-is-active')]//*[text()=\"Banner\"]/ancestor::*[contains(@class, \"ui-draggable\")]", "(//div[contains(@class, \"pagebuilder-content-type\") and contains(concat(' ', @class, ' '), ' pagebuilder-row ')])[1]/descendant::div[contains(@class, \"element-children\")]"); // stepKey: dropContentTypeIntoStageDragBannerIntoStage
		$I->waitForPageLoad(30); // stepKey: waitForAnimationDragBannerIntoStage
		$I->dontSeeJsError(); // stepKey: doNotSeeJSErrorInConsoleDragBannerIntoStage
		$I->waitForElementVisible("//div[contains(@class, \"pagebuilder-content-type\") and contains(concat(' ', @class, ' '), ' pagebuilder-banner ')]", 10); // stepKey: waitForContentTypeInStageDragBannerIntoStage
		$I->comment("Exiting Action Group [dragBannerIntoStage] dragContentTypeToStage");
		$I->comment("Entering Action Group [openEditMenuOnStage] openPageBuilderEditPanel");
		$I->moveMouseOver("//div[contains(@class,'stage-is-active')]//*[@id=\"search-content-types-input\"]"); // stepKey: moveMouseToSearchPanelOpenEditMenuOnStage
		$I->waitForPageLoad(30); // stepKey: moveMouseToSearchPanelOpenEditMenuOnStageWaitForPageLoad
		$I->waitForElementVisible("//div[contains(@class, \"pagebuilder-content-type\") and contains(concat(' ', @class, ' '), ' pagebuilder-banner ')]", 10); // stepKey: waitForContentTypeInStageVisibleOpenEditMenuOnStage
		$contentTypeLabelSelectorOpenEditMenuOnStage = $I->executeJS("return ['row', 'column'].include('banner') ? '//div[contains(@class, \"pagebuilder-display-label\") and contains(.,\"'+'banner'.toUpperCase()+'\")]' : ['tabs'].include('banner') ? '//ul[@data-element=\"navigation\"]' : '';"); // stepKey: contentTypeLabelSelectorOpenEditMenuOnStage
		$contentTypeSelectorOpenEditMenuOnStage = $I->executeJS("return ['row'].include('banner') ? '//div[contains(@class, \"pagebuilder-content-type-affordance\") and contains(concat(\" \", @class, \" \"), \" pagebuilder-affordance-banner \")]' : '//div[contains(@class, \"pagebuilder-content-type\") and contains(concat(\" \", @class, \" \"), \" pagebuilder-banner \")]';"); // stepKey: contentTypeSelectorOpenEditMenuOnStage
		$I->moveMouseOver("{$contentTypeSelectorOpenEditMenuOnStage}{$contentTypeLabelSelectorOpenEditMenuOnStage}", 10, 0); // stepKey: onMouseOverContentTypeStageOpenEditMenuOnStage
		$I->waitForPageLoad(30); // stepKey: waitForPageLoadOpenEditMenuOnStage
		$I->waitForElementVisible("(//div[contains(concat(' ', @class, ' '), ' pagebuilder-banner ')]//div[contains(@class, \"pagebuilder-options\")])[1]", 10); // stepKey: waitForOptionsOpenEditMenuOnStage
		$I->click("div.pagebuilder-content-type.pagebuilder-banner div.pagebuilder-options li.pagebuilder-options-link a.edit-content-type"); // stepKey: clickEditContentTypeOpenEditMenuOnStage
		$I->waitForPageLoad(30); // stepKey: waitForEditFormToLoadOpenEditMenuOnStage
		$I->waitForElementVisible("//div[contains(@class, \"pagebuilder_modal_form_pagebuilder_modal_form_modal\")]", 30); // stepKey: waitForEditFormOpenEditMenuOnStage
		$I->see("Edit Banner", "aside._show .modal-title[data-role='title']"); // stepKey: seeContentTypeNameInEditFormTitleOpenEditMenuOnStage
		$I->waitForLoadingMaskToDisappear(); // stepKey: waitForAnimation2OpenEditMenuOnStage
		$I->comment("Exiting Action Group [openEditMenuOnStage] openPageBuilderEditPanel");
		$I->comment("Entering Action Group [setAppearance] chooseVisualSelectOption");
		$I->waitForElement("//aside//div[@data-index=\"appearance_fieldset\"]/descendant::*[@name=\"appearance\"]", 2); // stepKey: waitForElementVisibleSetAppearance
		$I->seeElement("//li[@name=\"collage-right\"]"); // stepKey: seeVisualSelectSetAppearance
		$I->click("//div[@data-index=\"appearance_fieldset\"]//li[@name=\"collage-right\"]"); // stepKey: chooseVisualSelectOptionSetAppearance
		$I->waitForPageLoad(30); // stepKey: waitForPageLoadSetAppearance
		$I->comment("Exiting Action Group [setAppearance] chooseVisualSelectOption");
		$I->comment("Entering Action Group [enterMinHeightProperty] fillSlideOutPanelFieldGeneral");
		$I->comment("This action group does not assert against the section changed icon since this doesn't exist for General sections");
		$I->waitForPageLoad(30); // stepKey: waitForPageLoadEnterMinHeightProperty
		$I->waitForElement("//aside//div[@data-index=\"appearance_fieldset\"]/descendant::*[@name=\"min_height\"]", 2); // stepKey: waitForElementVisibleEnterMinHeightProperty
		$I->see("Minimum Height", "//aside//div[@data-index=\"appearance_fieldset\"]/descendant::div[@data-index=\"min_height\"]/descendant::label[not(contains(@style,\"display: none;\"))] | //aside//div[@data-index=\"appearance_fieldset\"]/descendant::*[@class=\"admin__field-label\" or @class=\"title\"]/descendant::span[text()=\"Minimum Height\"][not(ancestor::legend)]"); // stepKey: seePropertyLabelEnterMinHeightProperty
		$I->fillField("//aside//div[@data-index=\"appearance_fieldset\"]/descendant::*[@name=\"min_height\"]", "300px"); // stepKey: fillPropertyFieldEnterMinHeightProperty
		$I->click("//aside//div[@data-index=\"appearance_fieldset\"]/descendant::div[@data-index=\"min_height\"]/descendant::label[not(contains(@style,\"display: none;\"))] | //aside//div[@data-index=\"appearance_fieldset\"]/descendant::*[@class=\"admin__field-label\" or @class=\"title\"]/descendant::span[text()=\"Minimum Height\"][not(ancestor::legend)]"); // stepKey: clickOnFieldLabelEnterMinHeightProperty
		$I->comment("Exiting Action Group [enterMinHeightProperty] fillSlideOutPanelFieldGeneral");
		$I->comment("Set Edit Panel");
		$I->comment("Entering Action Group [setBackgroundType] chooseVisualSelectOption");
		$I->waitForElement("//aside//div[@data-index=\"background\"]/descendant::*[@name=\"background_type\"]", 2); // stepKey: waitForElementVisibleSetBackgroundType
		$I->seeElement("//li[@name=\"video\"]"); // stepKey: seeVisualSelectSetBackgroundType
		$I->click("//div[@data-index=\"background\"]//li[@name=\"video\"]"); // stepKey: chooseVisualSelectOptionSetBackgroundType
		$I->waitForPageLoad(30); // stepKey: waitForPageLoadSetBackgroundType
		$I->comment("Exiting Action Group [setBackgroundType] chooseVisualSelectOption");
		$I->comment("Entering Action Group [enterVideoUrlValid] fillSlideOutPanelFieldGeneral");
		$I->comment("This action group does not assert against the section changed icon since this doesn't exist for General sections");
		$I->waitForPageLoad(30); // stepKey: waitForPageLoadEnterVideoUrlValid
		$I->waitForElement("//aside//div[@data-index=\"background\"]/descendant::*[@name=\"video_source\"]", 2); // stepKey: waitForElementVisibleEnterVideoUrlValid
		$I->see("Video URL", "//aside//div[@data-index=\"background\"]/descendant::div[@data-index=\"video_source\"]/descendant::label[not(contains(@style,\"display: none;\"))] | //aside//div[@data-index=\"background\"]/descendant::*[@class=\"admin__field-label\" or @class=\"title\"]/descendant::span[text()=\"Video URL\"][not(ancestor::legend)]"); // stepKey: seePropertyLabelEnterVideoUrlValid
		$I->fillField("//aside//div[@data-index=\"background\"]/descendant::*[@name=\"video_source\"]", "https://www.youtube.com/embed/slOtnjsbff0"); // stepKey: fillPropertyFieldEnterVideoUrlValid
		$I->click("//aside//div[@data-index=\"background\"]/descendant::div[@data-index=\"video_source\"]/descendant::label[not(contains(@style,\"display: none;\"))] | //aside//div[@data-index=\"background\"]/descendant::*[@class=\"admin__field-label\" or @class=\"title\"]/descendant::span[text()=\"Video URL\"][not(ancestor::legend)]"); // stepKey: clickOnFieldLabelEnterVideoUrlValid
		$I->comment("Exiting Action Group [enterVideoUrlValid] fillSlideOutPanelFieldGeneral");
		$I->comment("Entering Action Group [disableInfiniteLoop] conditionalClickSlideOutPanelFieldGeneral");
		$I->waitForElement("//aside//div[@data-index=\"background\"]/descendant::*[@name=\"video_loop\"]", 2); // stepKey: waitForElementVisibleDisableInfiniteLoop
		$I->conditionalClick("//*[@name='video_loop']/parent::*/label", "//input[@type='checkbox' and @name='video_loop' and @value='false']", false); // stepKey: conditionalClickAttributeDisableInfiniteLoop
		$I->waitForElementVisible("//input[@type='checkbox' and @name='video_loop' and @value='false']", 30); // stepKey: waitForAttributeStateChangeDisableInfiniteLoop
		$I->comment("Exiting Action Group [disableInfiniteLoop] conditionalClickSlideOutPanelFieldGeneral");
		$I->comment("Entering Action Group [disableLazyLoad] conditionalClickSlideOutPanelFieldGeneral");
		$I->waitForElement("//aside//div[@data-index=\"background\"]/descendant::*[@name=\"video_lazy_load\"]", 2); // stepKey: waitForElementVisibleDisableLazyLoad
		$I->conditionalClick("//*[@name='video_lazy_load']/parent::*/label", "//input[@type='checkbox' and @name='video_lazy_load' and @value='false']", false); // stepKey: conditionalClickAttributeDisableLazyLoad
		$I->waitForElementVisible("//input[@type='checkbox' and @name='video_lazy_load' and @value='false']", 30); // stepKey: waitForAttributeStateChangeDisableLazyLoad
		$I->comment("Exiting Action Group [disableLazyLoad] conditionalClickSlideOutPanelFieldGeneral");
		$I->comment("Entering Action Group [disablePlayOnlyWhenVisible] conditionalClickSlideOutPanelFieldGeneral");
		$I->waitForElement("//aside//div[@data-index=\"background\"]/descendant::*[@name=\"video_play_only_visible\"]", 2); // stepKey: waitForElementVisibleDisablePlayOnlyWhenVisible
		$I->conditionalClick("//*[@name='video_play_only_visible']/parent::*/label", "//input[@type='checkbox' and @name='video_play_only_visible' and @value='false']", false); // stepKey: conditionalClickAttributeDisablePlayOnlyWhenVisible
		$I->waitForElementVisible("//input[@type='checkbox' and @name='video_play_only_visible' and @value='false']", 30); // stepKey: waitForAttributeStateChangeDisablePlayOnlyWhenVisible
		$I->comment("Exiting Action Group [disablePlayOnlyWhenVisible] conditionalClickSlideOutPanelFieldGeneral");
		$I->comment("Entering Action Group [selectShowButton] selectSlideOutPanelField");
		$I->waitForElementVisible("//aside//div[@data-index=\"contents\"]/descendant::*[@name=\"show_button\"]", 2); // stepKey: waitForElementVisibleSelectShowButton
		$I->selectOption("//aside//div[@data-index=\"contents\"]/descendant::*[@name=\"show_button\"]", "always"); // stepKey: selectPropertyFieldSelectShowButton
		$I->click("//aside//div[@data-index=\"contents\"]/descendant::div[@data-index=\"show_button\"]/descendant::label[not(contains(@style,\"display: none;\"))] | //aside//div[@data-index=\"contents\"]/descendant::*[@class=\"admin__field-label\" or @class=\"title\"]/descendant::span[text()=\"Show Button\"][not(ancestor::legend)]"); // stepKey: clickOnFieldLabelSelectShowButton
		$I->waitForElementVisible("[data-index='contents'] ._changed .admin__page-nav-item-message-icon", 2); // stepKey: waitForSectionChangedIconSelectShowButton
		$I->comment("Exiting Action Group [selectShowButton] selectSlideOutPanelField");
		$I->comment("Entering Action Group [saveEditPanelSettings] saveEditPanelSettings");
		$I->waitForPageLoad(30); // stepKey: waitForPageLoadSaveEditPanelSettings
		$I->waitForElementVisible(".modal-header .page-main-actions [data-form-role='save']", 5); // stepKey: waitForSaveButtonSaveEditPanelSettings
		$I->waitForPageLoad(30); // stepKey: waitForSaveButtonSaveEditPanelSettingsWaitForPageLoad
		$I->click(".modal-header .page-main-actions [data-form-role='save']"); // stepKey: clickSaveButtonSaveEditPanelSettings
		$I->waitForPageLoad(30); // stepKey: clickSaveButtonSaveEditPanelSettingsWaitForPageLoad
		$I->waitForPageLoad(30); // stepKey: waitForStageToLoadSaveEditPanelSettings
		$I->waitForElementNotVisible("//div[contains(@class, \"pagebuilder_modal_form_pagebuilder_modal_form_modal\")]", 5); // stepKey: waitForEditFormNotVisibleSaveEditPanelSettings
		$I->waitForElementVisible("#save-button", 10); // stepKey: waitForCmsPageSaveButtonSaveEditPanelSettings
		$I->waitForPageLoad(10); // stepKey: waitForCmsPageSaveButtonSaveEditPanelSettingsWaitForPageLoad
		$I->comment("Exiting Action Group [saveEditPanelSettings] saveEditPanelSettings");
		$I->comment("Validate Stage");
		$I->comment("Entering Action Group [validateStage1] validateVideoBackgroundWithOnlyVideoUrl");
		$I->waitForPageLoad(30); // stepKey: waitForPageLoadValidateStage1
		$I->waitForElementVisible("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper'][@data-background-type='video']", 30); // stepKey: waitForVideoBackgroundValidateStage1
		$grabBackgroundColorValueValidateStage1 = $I->executeJS("return window.getComputedStyle(document.evaluate('(//div[contains(@class,\"pagebuilder-banner\") and @data-element=\"main\"])[1]//div[@data-element=\"wrapper\"]', document.body).iterateNext()).backgroundColor"); // stepKey: grabBackgroundColorValueValidateStage1
		$I->assertEquals("rgb(250, 250, 250)", $grabBackgroundColorValueValidateStage1); // stepKey: dontSeeBackgroundColorInDOMValidateStage1
		$I->waitForElementVisible("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper']//div[contains(@id,'jarallax-container')]//iframe|(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper']//div[contains(@id,'jarallax-container')]//video", 30); // stepKey: waitForVideoVisibleValidateStage1
		$I->waitForElement("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper']//div[contains(@id,'jarallax-container')]//*[contains(@src,'https://www.youtube.com/embed/slOtnjsbff0')]", 30); // stepKey: waitForVideoUrlValidateStage1
		$jarallaxStyleValidateStage1 = $I->grabAttributeFrom("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper']//div[contains(@id,'jarallax-container')]", "style"); // stepKey: jarallaxStyleValidateStage1
		$I->assertStringContainsString("height: 100%;", $jarallaxStyleValidateStage1); // stepKey: assertHeightValidateStage1
		$I->assertStringContainsString("width: 100%;", $jarallaxStyleValidateStage1); // stepKey: assertWidthValidateStage1
		$I->assertStringContainsString("overflow: hidden;", $jarallaxStyleValidateStage1); // stepKey: assertOverflowHiddenValidateStage1
		$videoStyleValidateStage1 = $I->grabAttributeFrom("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper']//div[contains(@id,'jarallax-container')]//iframe|(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper']//div[contains(@id,'jarallax-container')]//video", "style"); // stepKey: videoStyleValidateStage1
		$I->assertStringContainsString("position: absolute;", $videoStyleValidateStage1); // stepKey: assertVideoPositionValidateStage1
		$I->assertStringContainsString("transform: translate3d(", $videoStyleValidateStage1); // stepKey: assertVideoPlayingValidateStage1
		$I->dontSeeElementInDOM("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper']//div[@data-element='video_overlay']"); // stepKey: dontSeeOverlayColorInDOMValidateStage1
		$I->waitForElementVisible("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper'][@data-video-loop='false']", 30); // stepKey: waitForInfiniteLoopValidateStage1
		$I->waitForElementVisible("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper'][@data-video-lazy-load='false']", 30); // stepKey: waitForLazyLoadValidateStage1
		$I->waitForElementVisible("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper'][@data-video-play-only-visible='false']", 30); // stepKey: waitForPlayOnlyWhenVisibleValidateStage1
		$I->waitForElement("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper']//div[contains(@id,'jarallax-container')]//div", 30); // stepKey: waitForNoFallbackImageValidateStage1
		$I->dontSeeElement("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper']//div[contains(@id,'jarallax-container')]//div"); // stepKey: dontSeeFallbackImageValidateStage1
		$I->dontSeeElementInDOM("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper']//div[contains(@id,'jarallax-container')]//img"); // stepKey: dontSeeFallbackImageInDOMValidateStage1
		$I->comment("Exiting Action Group [validateStage1] validateVideoBackgroundWithOnlyVideoUrl");
		$I->comment("Entering Action Group [inlineEdit] inlineEditWYSIWYGFromStage");
		$I->click("(//div[@data-content-type='banner'])[1]//div[contains(@class,'inline-wysiwyg')]|(//div[@data-content-type='banner' and contains(@class,'inline-wysiwyg')])[1]"); // stepKey: focusOnEditorAreaInlineEdit
		$I->waitForElementVisible("(//div[@data-content-type='banner'])[1]//div[contains(@class,'tox-tinymce-inline')]|(//div[@data-content-type='banner'])[1][following-sibling::div[contains(@class,'tox-tinymce-inline')]]", 30); // stepKey: waitForEditorPanelInlineEdit
		$I->pressKey("(//div[@data-content-type='banner'])[1]//div[contains(@class,'inline-wysiwyg')]|(//div[@data-content-type='banner' and contains(@class,'inline-wysiwyg')])[1]", "Good Night!"); // stepKey: enterContentIntoEditorInlineEdit
		$I->waitForPageLoad(30); // stepKey: waitForPageLoadInlineEdit
		$I->click("//div[contains(@class,'stage-is-active')]//*[@id=\"search-content-types-input\"]"); // stepKey: loseFocusFromEditorInlineEdit
		$I->waitForPageLoad(30); // stepKey: loseFocusFromEditorInlineEditWaitForPageLoad
		$I->comment("Exiting Action Group [inlineEdit] inlineEditWYSIWYGFromStage");
		$I->comment("Entering Action Group [inlineEditBannerButton] inlineEditSlideOrBannerButton");
		$I->waitForElementVisible("(//div[contains(@class,'pagebuilder-banner') and contains(@class,'pagebuilder-content-type')])[1]//a//span[@contenteditable='true']", 30); // stepKey: waitForButtonVisibleInlineEditBannerButton
		$I->click("(//div[contains(@class,'pagebuilder-banner') and contains(@class,'pagebuilder-content-type')])[1]//a//span[@contenteditable='true']"); // stepKey: clickButtonToEditInlineEditBannerButton
		$I->pressKey("(//div[contains(@class,'pagebuilder-banner') and contains(@class,'pagebuilder-content-type')])[1]//a//span[@contenteditable='true']", "Good Morning!"); // stepKey: enterButtonTextInlineEditBannerButton
		$I->waitForElementVisible("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//a//span[.='Good Morning!']", 30); // stepKey: seeButtonTextInlineEditBannerButton
		$I->click("//div[contains(@class,'stage-is-active')]//*[@id=\"search-content-types-input\"]"); // stepKey: unFocusLiveEditInlineEditBannerButton
		$I->waitForPageLoad(30); // stepKey: unFocusLiveEditInlineEditBannerButtonWaitForPageLoad
		$I->waitForPageLoad(30); // stepKey: waitForUnFocusInlineEditBannerButton
		$I->comment("Exiting Action Group [inlineEditBannerButton] inlineEditSlideOrBannerButton");
		$I->waitForElementVisible("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='content']//*[contains(text(),'Good Night!')]", 30); // stepKey: waitForMessageContentStage
		$I->waitForElementVisible("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//a//span[.='Good Morning!']", 30); // stepKey: waitForButtonTextStage
		$I->comment("Entering Action Group [exitPageBuilderFullScreen] exitPageBuilderFullScreen");
		$I->waitForElementVisible(".pagebuilder-header .icon-pagebuilder-fullscreen-exit", 30); // stepKey: waitForExitPageBuilderFullScreenButtonExitPageBuilderFullScreen
		$I->click(".pagebuilder-header .icon-pagebuilder-fullscreen-exit"); // stepKey: exitPageBuilderFullScreenExitPageBuilderFullScreen
		$I->waitForPageLoad(30); // stepKey: waitForExitFullScreenExitPageBuilderFullScreen
		$I->dontSeeElementInDOM(".pagebuilder-header .icon-pagebuilder-fullscreen-exit"); // stepKey: dontSeeExitPageBuilderFullScreenButtonExitPageBuilderFullScreen
		$I->comment("Exiting Action Group [exitPageBuilderFullScreen] exitPageBuilderFullScreen");
		$I->comment("Entering Action Group [saveAndContinueEditCmsPage] SaveAndContinueEditCmsPageActionGroup");
		$I->waitForElementVisible("#save-button", 10); // stepKey: waitForSaveAndContinueVisibilitySaveAndContinueEditCmsPage
		$I->waitForPageLoad(10); // stepKey: waitForSaveAndContinueVisibilitySaveAndContinueEditCmsPageWaitForPageLoad
		$I->click("#save-button"); // stepKey: clickSaveAndContinueEditCmsPageSaveAndContinueEditCmsPage
		$I->waitForPageLoad(10); // stepKey: clickSaveAndContinueEditCmsPageSaveAndContinueEditCmsPageWaitForPageLoad
		$I->waitForPageLoad(30); // stepKey: waitForCmsPageLoadSaveAndContinueEditCmsPage
		$I->waitForElementVisible(".page-header .page-title", 1); // stepKey: waitForCmsPageSaveButtonSaveAndContinueEditCmsPage
		$I->waitForLoadingMaskToDisappear(); // stepKey: waitForLoadingMaskSaveAndContinueEditCmsPage
		$I->comment("Exiting Action Group [saveAndContinueEditCmsPage] SaveAndContinueEditCmsPageActionGroup");
		$I->comment("Entering Action Group [switchToPageBuilderStage2] switchToPageBuilderStage");
		$I->waitForElementVisible("div[data-index=content]", 30); // stepKey: waitForSectionSwitchToPageBuilderStage2
		$I->conditionalClick("div[data-index=content]", "div[data-index=content]._show", false); // stepKey: expandSectionSwitchToPageBuilderStage2
		$I->waitForPageLoad(30); // stepKey: waitForStageToLoadSwitchToPageBuilderStage2
		$I->waitForElementVisible("div.stage-content-snapshot", 30); // stepKey: waitForSnapshotSwitchToPageBuilderStage2
		$I->waitForElementVisible("//button/span[contains(text(), 'Edit with Page Builder')]", 30); // stepKey: waitForEditButtonSwitchToPageBuilderStage2
		$I->click("//button/span[contains(text(), 'Edit with Page Builder')]"); // stepKey: clickEditButtonSwitchToPageBuilderStage2
		$I->waitForPageLoad(30); // stepKey: waitForFullScreenAnimationSwitchToPageBuilderStage2
		$I->waitForElementNotVisible("div.pagebuilder-stage-loading", 30); // stepKey: waitForStageLoadingGraphicNotVisibleSwitchToPageBuilderStage2
		$I->waitForElementVisible("(//div[contains(@class,\"pagebuilder-content-type\") and contains(@class,\"pagebuilder-root-container\")])[1]", 30); // stepKey: waitForPageBuilderRootContainerSwitchToPageBuilderStage2
		$I->comment("removing deprecated element");
		$I->comment("Exiting Action Group [switchToPageBuilderStage2] switchToPageBuilderStage");
		$I->comment("Validate Stage After Save");
		$I->scrollTo("div[data-index=content]"); // stepKey: scrollBanner1Stage
		$I->comment("Entering Action Group [validateStage2] validateVideoBackgroundWithOnlyVideoUrl");
		$I->waitForPageLoad(30); // stepKey: waitForPageLoadValidateStage2
		$I->waitForElementVisible("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper'][@data-background-type='video']", 30); // stepKey: waitForVideoBackgroundValidateStage2
		$grabBackgroundColorValueValidateStage2 = $I->executeJS("return window.getComputedStyle(document.evaluate('(//div[contains(@class,\"pagebuilder-banner\") and @data-element=\"main\"])[1]//div[@data-element=\"wrapper\"]', document.body).iterateNext()).backgroundColor"); // stepKey: grabBackgroundColorValueValidateStage2
		$I->assertEquals("rgb(250, 250, 250)", $grabBackgroundColorValueValidateStage2); // stepKey: dontSeeBackgroundColorInDOMValidateStage2
		$I->waitForElementVisible("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper']//div[contains(@id,'jarallax-container')]//iframe|(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper']//div[contains(@id,'jarallax-container')]//video", 30); // stepKey: waitForVideoVisibleValidateStage2
		$I->waitForElement("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper']//div[contains(@id,'jarallax-container')]//*[contains(@src,'https://www.youtube.com/embed/slOtnjsbff0')]", 30); // stepKey: waitForVideoUrlValidateStage2
		$jarallaxStyleValidateStage2 = $I->grabAttributeFrom("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper']//div[contains(@id,'jarallax-container')]", "style"); // stepKey: jarallaxStyleValidateStage2
		$I->assertStringContainsString("height: 100%;", $jarallaxStyleValidateStage2); // stepKey: assertHeightValidateStage2
		$I->assertStringContainsString("width: 100%;", $jarallaxStyleValidateStage2); // stepKey: assertWidthValidateStage2
		$I->assertStringContainsString("overflow: hidden;", $jarallaxStyleValidateStage2); // stepKey: assertOverflowHiddenValidateStage2
		$videoStyleValidateStage2 = $I->grabAttributeFrom("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper']//div[contains(@id,'jarallax-container')]//iframe|(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper']//div[contains(@id,'jarallax-container')]//video", "style"); // stepKey: videoStyleValidateStage2
		$I->assertStringContainsString("position: absolute;", $videoStyleValidateStage2); // stepKey: assertVideoPositionValidateStage2
		$I->assertStringContainsString("transform: translate3d(", $videoStyleValidateStage2); // stepKey: assertVideoPlayingValidateStage2
		$I->dontSeeElementInDOM("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper']//div[@data-element='video_overlay']"); // stepKey: dontSeeOverlayColorInDOMValidateStage2
		$I->waitForElementVisible("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper'][@data-video-loop='false']", 30); // stepKey: waitForInfiniteLoopValidateStage2
		$I->waitForElementVisible("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper'][@data-video-lazy-load='false']", 30); // stepKey: waitForLazyLoadValidateStage2
		$I->waitForElementVisible("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper'][@data-video-play-only-visible='false']", 30); // stepKey: waitForPlayOnlyWhenVisibleValidateStage2
		$I->waitForElement("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper']//div[contains(@id,'jarallax-container')]//div", 30); // stepKey: waitForNoFallbackImageValidateStage2
		$I->dontSeeElement("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper']//div[contains(@id,'jarallax-container')]//div"); // stepKey: dontSeeFallbackImageValidateStage2
		$I->dontSeeElementInDOM("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='wrapper']//div[contains(@id,'jarallax-container')]//img"); // stepKey: dontSeeFallbackImageInDOMValidateStage2
		$I->comment("Exiting Action Group [validateStage2] validateVideoBackgroundWithOnlyVideoUrl");
		$I->waitForElementVisible("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//div[@data-element='content']//*[contains(text(),'Good Night!')]", 30); // stepKey: waitForMessageContentStage2
		$I->waitForElementVisible("(//div[contains(@class,'pagebuilder-banner') and @data-element='main'])[1]//a//span[.='Good Morning!']", 30); // stepKey: waitForButtonTextStage2
		$I->comment("Validate Edit Panel After Save");
		$I->comment("Entering Action Group [openEditPanelAfterSave] openPageBuilderEditPanel");
		$I->moveMouseOver("//div[contains(@class,'stage-is-active')]//*[@id=\"search-content-types-input\"]"); // stepKey: moveMouseToSearchPanelOpenEditPanelAfterSave
		$I->waitForPageLoad(30); // stepKey: moveMouseToSearchPanelOpenEditPanelAfterSaveWaitForPageLoad
		$I->waitForElementVisible("//div[contains(@class, \"pagebuilder-content-type\") and contains(concat(' ', @class, ' '), ' pagebuilder-banner ')]", 10); // stepKey: waitForContentTypeInStageVisibleOpenEditPanelAfterSave
		$contentTypeLabelSelectorOpenEditPanelAfterSave = $I->executeJS("return ['row', 'column'].include('banner') ? '//div[contains(@class, \"pagebuilder-display-label\") and contains(.,\"'+'banner'.toUpperCase()+'\")]' : ['tabs'].include('banner') ? '//ul[@data-element=\"navigation\"]' : '';"); // stepKey: contentTypeLabelSelectorOpenEditPanelAfterSave
		$contentTypeSelectorOpenEditPanelAfterSave = $I->executeJS("return ['row'].include('banner') ? '//div[contains(@class, \"pagebuilder-content-type-affordance\") and contains(concat(\" \", @class, \" \"), \" pagebuilder-affordance-banner \")]' : '//div[contains(@class, \"pagebuilder-content-type\") and contains(concat(\" \", @class, \" \"), \" pagebuilder-banner \")]';"); // stepKey: contentTypeSelectorOpenEditPanelAfterSave
		$I->moveMouseOver("{$contentTypeSelectorOpenEditPanelAfterSave}{$contentTypeLabelSelectorOpenEditPanelAfterSave}", null, null); // stepKey: onMouseOverContentTypeStageOpenEditPanelAfterSave
		$I->waitForPageLoad(30); // stepKey: waitForPageLoadOpenEditPanelAfterSave
		$I->waitForElementVisible("(//div[contains(concat(' ', @class, ' '), ' pagebuilder-banner ')]//div[contains(@class, \"pagebuilder-options\")])[1]", 10); // stepKey: waitForOptionsOpenEditPanelAfterSave
		$I->click("div.pagebuilder-content-type.pagebuilder-banner div.pagebuilder-options li.pagebuilder-options-link a.edit-content-type"); // stepKey: clickEditContentTypeOpenEditPanelAfterSave
		$I->waitForPageLoad(30); // stepKey: waitForEditFormToLoadOpenEditPanelAfterSave
		$I->waitForElementVisible("//div[contains(@class, \"pagebuilder_modal_form_pagebuilder_modal_form_modal\")]", 30); // stepKey: waitForEditFormOpenEditPanelAfterSave
		$I->see("Edit Banner", "aside._show .modal-title[data-role='title']"); // stepKey: seeContentTypeNameInEditFormTitleOpenEditPanelAfterSave
		$I->waitForLoadingMaskToDisappear(); // stepKey: waitForAnimation2OpenEditPanelAfterSave
		$I->comment("Exiting Action Group [openEditPanelAfterSave] openPageBuilderEditPanel");
		$I->comment("Entering Action Group [validateBackgroundColorEmptyAfterSave] seeInFieldSlideOutProperty");
		$I->waitForElement("//aside//div[@data-index=\"background\"]/descendant::*[@name=\"background_color\"]", 2); // stepKey: waitForElementVisibleValidateBackgroundColorEmptyAfterSave
		$I->see("Background Color", "//aside//div[@data-index=\"background\"]/descendant::div[@data-index=\"background_color\"]/descendant::label[not(contains(@style,\"display: none;\"))] | //aside//div[@data-index=\"background\"]/descendant::*[@class=\"admin__field-label\" or @class=\"title\"]/descendant::span[text()=\"Background Color\"][not(ancestor::legend)]"); // stepKey: seePropertyLabelValidateBackgroundColorEmptyAfterSave
		$I->seeInField("//aside//div[@data-index=\"background\"]/descendant::*[@name=\"background_color\"]", ""); // stepKey: seeInFieldPropertyValidateBackgroundColorEmptyAfterSave
		$I->comment("Exiting Action Group [validateBackgroundColorEmptyAfterSave] seeInFieldSlideOutProperty");
		$I->comment("Entering Action Group [validateVideoUrlAfterSave] seeInFieldSlideOutProperty");
		$I->waitForElement("//aside//div[@data-index=\"background\"]/descendant::*[@name=\"video_source\"]", 2); // stepKey: waitForElementVisibleValidateVideoUrlAfterSave
		$I->see("Video URL", "//aside//div[@data-index=\"background\"]/descendant::div[@data-index=\"video_source\"]/descendant::label[not(contains(@style,\"display: none;\"))] | //aside//div[@data-index=\"background\"]/descendant::*[@class=\"admin__field-label\" or @class=\"title\"]/descendant::span[text()=\"Video URL\"][not(ancestor::legend)]"); // stepKey: seePropertyLabelValidateVideoUrlAfterSave
		$I->seeInField("//aside//div[@data-index=\"background\"]/descendant::*[@name=\"video_source\"]", "https://www.youtube.com/embed/slOtnjsbff0"); // stepKey: seeInFieldPropertyValidateVideoUrlAfterSave
		$I->comment("Exiting Action Group [validateVideoUrlAfterSave] seeInFieldSlideOutProperty");
		$I->comment("Entering Action Group [validateOverlayColorEmptyAfterSave] seeInFieldSlideOutProperty");
		$I->waitForElement("//aside//div[@data-index=\"background\"]/descendant::*[@name=\"video_overlay_color\"]", 2); // stepKey: waitForElementVisibleValidateOverlayColorEmptyAfterSave
		$I->see("Overlay Color", "//aside//div[@data-index=\"background\"]/descendant::div[@data-index=\"video_overlay_color\"]/descendant::label[not(contains(@style,\"display: none;\"))] | //aside//div[@data-index=\"background\"]/descendant::*[@class=\"admin__field-label\" or @class=\"title\"]/descendant::span[text()=\"Overlay Color\"][not(ancestor::legend)]"); // stepKey: seePropertyLabelValidateOverlayColorEmptyAfterSave
		$I->seeInField("//aside//div[@data-index=\"background\"]/descendant::*[@name=\"video_overlay_color\"]", ""); // stepKey: seeInFieldPropertyValidateOverlayColorEmptyAfterSave
		$I->comment("Exiting Action Group [validateOverlayColorEmptyAfterSave] seeInFieldSlideOutProperty");
		$I->comment("Entering Action Group [validateInfiniteLoopAfterSave] seeOptionIsNotCheckedSlideOutProperty");
		$I->waitForElement("//aside//div[@data-index=\"background\"]/descendant::*[@name=\"video_loop\"]", 2); // stepKey: waitForElementVisibleValidateInfiniteLoopAfterSave
		$I->dontSeeCheckboxIsChecked("//aside//div[@data-index=\"background\"]/descendant::*[@name=\"video_loop\"]"); // stepKey: dontSeeOptionIsCheckedPropertyValidateInfiniteLoopAfterSave
		$I->comment("Exiting Action Group [validateInfiniteLoopAfterSave] seeOptionIsNotCheckedSlideOutProperty");
		$I->comment("Entering Action Group [validateLazyLoadAfterSave] seeOptionIsNotCheckedSlideOutProperty");
		$I->waitForElement("//aside//div[@data-index=\"background\"]/descendant::*[@name=\"video_lazy_load\"]", 2); // stepKey: waitForElementVisibleValidateLazyLoadAfterSave
		$I->dontSeeCheckboxIsChecked("//aside//div[@data-index=\"background\"]/descendant::*[@name=\"video_lazy_load\"]"); // stepKey: dontSeeOptionIsCheckedPropertyValidateLazyLoadAfterSave
		$I->comment("Exiting Action Group [validateLazyLoadAfterSave] seeOptionIsNotCheckedSlideOutProperty");
		$I->comment("Entering Action Group [validatePlayOnlyWhenVisibleAfterSave] seeOptionIsNotCheckedSlideOutProperty");
		$I->waitForElement("//aside//div[@data-index=\"background\"]/descendant::*[@name=\"video_play_only_visible\"]", 2); // stepKey: waitForElementVisibleValidatePlayOnlyWhenVisibleAfterSave
		$I->dontSeeCheckboxIsChecked("//aside//div[@data-index=\"background\"]/descendant::*[@name=\"video_play_only_visible\"]"); // stepKey: dontSeeOptionIsCheckedPropertyValidatePlayOnlyWhenVisibleAfterSave
		$I->comment("Exiting Action Group [validatePlayOnlyWhenVisibleAfterSave] seeOptionIsNotCheckedSlideOutProperty");
		$I->comment("Entering Action Group [validateNoFallbackImageAfterSave] seeNoImageUploadedOnSlideOut");
		$I->waitForElement("//aside//div[@data-index=\"background\"]/descendant::*[@name=\"video_fallback_image\"]", 30); // stepKey: waitForElementValidateNoFallbackImageAfterSave
		$I->waitForElementVisible("//span[text()='Fallback Image']//parent::label//following-sibling::div//p[text()='Browse to find or drag image here']", 30); // stepKey: seeNoUploadedFileValidateNoFallbackImageAfterSave
		$I->comment("Exiting Action Group [validateNoFallbackImageAfterSave] seeNoImageUploadedOnSlideOut");
		$I->comment("Validate Storefront");
		$I->comment("Entering Action Group [navigateToStorefront] NavigateToStorefrontForCreatedPageActionGroup");
		$I->amOnPage($I->retrieveEntityField('createCMSPage', 'identifier', 'test')); // stepKey: goToStorefrontNavigateToStorefront
		$I->waitForPageLoad(30); // stepKey: waitForPageLoadNavigateToStorefront
		$I->comment("Exiting Action Group [navigateToStorefront] NavigateToStorefrontForCreatedPageActionGroup");
		$I->comment("Entering Action Group [validateStorefront] validateVideoBackgroundWithOnlyVideoUrl");
		$I->waitForPageLoad(30); // stepKey: waitForPageLoadValidateStorefront
		$I->waitForElementVisible("(//div[@data-content-type='banner'])[1]//div[@data-background-type='video']", 30); // stepKey: waitForVideoBackgroundValidateStorefront
		$grabBackgroundColorValueValidateStorefront = $I->executeJS("return window.getComputedStyle(document.evaluate('(//div[@data-content-type=\"banner\"])[1]//div[@data-element=\"wrapper\"]', document.body).iterateNext()).backgroundColor"); // stepKey: grabBackgroundColorValueValidateStorefront
		$I->assertEquals("rgba(0, 0, 0, 0)", $grabBackgroundColorValueValidateStorefront); // stepKey: dontSeeBackgroundColorInDOMValidateStorefront
		$I->waitForElementVisible("(//div[@data-content-type='banner'])[1]//div[contains(@id,'jarallax-container')]//iframe|(//div[@data-content-type='banner'])[1]//div[contains(@id,'jarallax-container')]//video", 30); // stepKey: waitForVideoVisibleValidateStorefront
		$I->waitForElement("(//div[@data-content-type='banner'])[1]//div[contains(@id,'jarallax-container')]//*[contains(@src,'https://www.youtube.com/embed/slOtnjsbff0')]", 30); // stepKey: waitForVideoUrlValidateStorefront
		$jarallaxStyleValidateStorefront = $I->grabAttributeFrom("(//div[@data-content-type='banner'])[1]//div[contains(@id,'jarallax-container')]", "style"); // stepKey: jarallaxStyleValidateStorefront
		$I->assertStringContainsString("height: 100%;", $jarallaxStyleValidateStorefront); // stepKey: assertHeightValidateStorefront
		$I->assertStringContainsString("width: 100%;", $jarallaxStyleValidateStorefront); // stepKey: assertWidthValidateStorefront
		$I->assertStringContainsString("overflow: hidden;", $jarallaxStyleValidateStorefront); // stepKey: assertOverflowHiddenValidateStorefront
		$videoStyleValidateStorefront = $I->grabAttributeFrom("(//div[@data-content-type='banner'])[1]//div[contains(@id,'jarallax-container')]//iframe|(//div[@data-content-type='banner'])[1]//div[contains(@id,'jarallax-container')]//video", "style"); // stepKey: videoStyleValidateStorefront
		$I->assertStringContainsString("position: absolute;", $videoStyleValidateStorefront); // stepKey: assertVideoPositionValidateStorefront
		$I->assertStringContainsString("transform: translate3d(", $videoStyleValidateStorefront); // stepKey: assertVideoPlayingValidateStorefront
		$I->dontSeeElementInDOM("(//div[@data-content-type='banner'])[1]//div[@data-element='video_overlay']"); // stepKey: dontSeeOverlayColorInDOMValidateStorefront
		$I->waitForElementVisible("(//div[@data-content-type='banner'])[1]//div[@data-element='wrapper' and @data-video-loop='false']", 30); // stepKey: waitForInfiniteLoopValidateStorefront
		$I->waitForElementVisible("(//div[@data-content-type='banner'])[1]//div[@data-element='wrapper' and @data-video-lazy-load='false']", 30); // stepKey: waitForLazyLoadValidateStorefront
		$I->waitForElementVisible("(//div[@data-content-type='banner'])[1]//div[@data-element='wrapper' and @data-video-play-only-visible='false']", 30); // stepKey: waitForPlayOnlyWhenVisibleValidateStorefront
		$I->waitForElement("(//div[@data-content-type='banner'])[1]//div[contains(@id,'jarallax-container')]//div", 30); // stepKey: waitForNoFallbackImageValidateStorefront
		$I->dontSeeElement("(//div[@data-content-type='banner'])[1]//div[contains(@id,'jarallax-container')]//div"); // stepKey: dontSeeFallbackImageValidateStorefront
		$I->dontSeeElementInDOM("(//div[@data-content-type='banner'])[1]//div[contains(@id,'jarallax-container')]//img"); // stepKey: dontSeeFallbackImageInDOMValidateStorefront
		$I->comment("Exiting Action Group [validateStorefront] validateVideoBackgroundWithOnlyVideoUrl");
		$I->waitForElementVisible("(//div[@data-content-type='banner'])[1]//div[@data-element='content']//*[contains(text(),'Good Night!')]", 30); // stepKey: waitForMessageContentStorefront
		$I->waitForElementVisible("(//div[@data-content-type='banner'])[1]//div[@data-element='wrapper']//button[.='Good Morning!']", 30); // stepKey: waitForButtonTextStorefront
	}
}
