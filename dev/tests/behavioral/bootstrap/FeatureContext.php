<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use Behat\Behat\Event\SuiteEvent;

use Behat\MinkExtension\Context\MinkContext;


use PhpImap\Mailbox as ImapMailbox;
use PhpImap\IncomingMail;
use PhpImap\IncomingMailAttachment;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class FeatureContext extends MinkContext
{
    const TIMEOUT_PAGE_LOAD         = 120;
    const TIMEOUT_ELEMENT_TO_APPEAR = 60;

    // 30 tries 10 seconds each ~ 5 minutes (+ connection and pull time)
    const MAIL_CHECK_TRIES_NUM      = 30;
    const MAIL_CHECK_TRY_SLEEP      = 5;

    /**
     * Initializes context.
     * Every scenario gets its own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        // Initialize your context here
    }

    /**
     * @BeforeScenario
     */
    public function beforeScenario()
    {
        // $this->getSession()->getDriver()->resizeWindow(1280, 768);
        $this->getSession()->maximizeWindow();
    }

    /**
     * @Then /^I wait for the suggestion box to appear$/
     */
    public function iWaitForTheSuggestionBoxToAppear()
    {
        $this->getSession()->wait(5000,
            "$('.suggestions-results').children().length > 0"
        );
    }

	/** Click on the element with the provided xpath query
	 *
	 * @When /^I click on the element with xpath "([^"]*)"$/
	 */
	public function iClickOnTheElementWithXPath($xpath)
	{
	    $session = $this->getSession(); // get the mink session
	    $element = $session->getPage()->find(
		'xpath',
		$session->getSelectorsHandler()->selectorToXpath('xpath', $xpath)
	    ); // runs the actual query and returns the element

	    // errors must not pass silently
	    if (null === $element) {
		throw new \InvalidArgumentException(sprintf('Could not evaluate XPath: "%s"', $xpath));
	    }

	    // ok, let's click on it
	    $element->click();

	}

    /** Wait for page to load
     *
     * @When /^I wait for page to load "([^"]*)"$/
     */
    public function iWaitForPageToLoad($pageName)
    {
        $timeOut = self::TIMEOUT_PAGE_LOAD;
        $timeEnd = time() + $timeOut;
        while (0 !== stripos($this->getSession()->getCurrentUrl(), sprintf('%s%s', $this->getMinkParameter('base_url'), $pageName))) {
            usleep(100);
            if (time() >= $timeEnd) {
                throw  new \Exception(sprintf('Load page timeout (%d seconds) exceeded', $timeOut));
            }
        }
    }

    /**
     * @Given /^I wait for element "([^"]*)" to appear$/
     */
    public function iWaitForElementToAppear($selector)
    {
        $this->waitForElementToAppear('css', $selector);
    }

    /**
     * @Given /^I wait for element with xpath "([^"]*)" to appear$/
     */
    public function iWaitForElementWithXpathToAppear($selector)
    {
        $this->waitForElementToAppear('xpath', $selector);
    }

    /**
     * @Then /^the element with xpath "([^"]*)" should contain "([^"]*)"$/
     */
    public function theElementWithXpathShouldContain($xpath, $string)
    {
        $this->assertSession()->elementTextContains('xpath', $xpath, $this->fixStepArgument($string));
    }


    /**
     * @Given /^I click on the element with xpath "\/\/\*\[@id="(\d+)"\]"$/
     */
    public function iClickOnTheElementWithXpathId($selector)
    {
        $this->getSession()->getPage()->find('xpath', $selector)->click();
    }

    /**
     * @Given /^I click on the element "([^"]*)"$/
     */
    public function iClickOnTheElement($selector)
    {
        if( ! $this->getSession()->getPage()->find('css', $selector))
            throw  new \Exception(sprintf('Element with css "%s" not found', $selector));

        $this->getSession()->getPage()->find('css', $selector)->click();
    }


    /************* protected ************/

    protected function waitForElementToAppear($method, $selector){
        $timeOut = self::TIMEOUT_ELEMENT_TO_APPEAR;
        $timeEnd = time() + $timeOut;
        while ( ! (
            $this->getSession()->getPage()->find($method, $selector)
            && $this->getSession()->getPage()->find($method, $selector)->isVisible()
        ) ) {
            usleep(10);
            if ( time() >= $timeEnd ) {
                throw  new \Exception(sprintf('Element with xpath "%s" didn\'t appear', $selector));
            }
        }
    }

    /**
     * @Given /^I wait for "([^"]*)" seconds$/
     */
    public function iWaitForSeconds($seconds)
    {
        sleep($seconds);
    }


    #=========== experimental ======#

    /**
     * @Given /^my main site is "([^"]*)"$/
     */
    public function myBaseUrlIs($url)
    {
        $this->setMinkParameters(array('base_url', $url));
    }

    /**
     * @Given /^I wait for element containing unique text "([^"]*)" to appear$/
     */
    public function iWaitForElementContainingUniqueTextToAppear($text)
    {
        $this->waitForElementContainingUniqueText($text);
    }

    /**
     * @Given /^I wait for element containing unique text "([^"]*)" to click$/
     */
    public function iWaitForElementContainingUniqueTextToClick($text)
    {
        $this->waitForElementContainingUniqueText($text)->click();
    }

    /**
     * @Given /^I click on element containing unique text "([^"]*)"$/
     */
    public function iClickOnElementContainingUniqueText($text){
        $element = $this->waitForElementContainingUniqueText($text);
        $element->click();
    }

    /**
     * @Given /^I should see element containing unique text "([^"]*)"$/
     */
    public function iShouldSeeElementContainingUniqueText($text)
    {
        $element = $this->waitForElementContainingUniqueText($text);
        if(!$element){
            throw  new \Exception(sprintf('Element containing unique text "%s" was not found', $text));
        }
    }

    /**
     * @Given /^I scroll "([^"]*)" into view$/
     */
    public function iScrollIntoView($elementId)
    {
        $function = <<<JS
(function(){
var elem = document.getElementById("$elementId");
elem.scrollIntoView(false);
})()
JS;
        try {
            $this->getSession()->executeScript($function);
        }
        catch(Exception $e) {
            throw new \Exception("ScrollIntoView failed");
        }
    }

    /**
     * @Given /^I switch to "([^"]*)" iframe$/
     */
    public function iSwitchToIframe($name)
    {
        $this->getSession()->switchToIFrame($name);
    }

    /**
     * @Given /^I switch to the main frame$/
     */
    public function iSwitchToTheMainFrame()
    {
        $this->getSession()->switchToIFrame();
    }


    /**
     * @When I scroll :elementId into view
     */
    public function scrollIntoView($elementId) {
        $function = <<<JS
(function(){
var elem = document.getElementById("$elementId");
elem.scrollIntoView(false);
})()
JS;
        try {
            $this->getSession()->executeScript($function);
        }
        catch(Exception $e) {
            throw new \Exception("ScrollIntoView failed");
        }
    }

    private function findElementContainingText($text){
        #$xpath = sprintf('//*[contains(text(),"%s")]', $text);
        $xpath = sprintf('//*[contains(translate(text(), "ABCDEFGHJIKLMNOPQRSTUVWXYZ", "abcdefghjiklmnopqrstuvwxyz"), "%s")]', strtolower($text));
        $elementList = $this->getSession()->getPage()->findAll('xpath', $xpath);
        foreach($elementList as $element){
            if($element->isVisible())
                return $element;
        }

        return null;
    }

    /**
     * @param $text
     * @throws Exception
     */
    protected function waitForElementContainingUniqueText($text)
    {
        $timeOut = self::TIMEOUT_ELEMENT_TO_APPEAR;
        $timeEnd = time() + $timeOut;
        while ( ! $this->findElementContainingText($text)) {
            usleep(10);
            if ( time() >= $timeEnd ) {
                throw  new \Exception(sprintf('Element containing unique text "%s" did not appear within %d seconds', $text, $timeOut));
            }
        }

        $element = $this->findElementContainingText($text);

        return $element;
    }


}
