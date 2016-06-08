<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Client\Driver\Selenium;

use Magento\Mtf\Config\DataInterface;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\DriverInterface;
use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\System\Event\EventManagerInterface;
use Magento\Mtf\Client\Driver\Selenium\Driver\PageLoaderInterface;

/**
 * Selenium Driver.
 */
class Driver implements DriverInterface
{
    /**
     * Driver configuration.
     *
     * @var DataInterface
     */
    protected $configuration;

    /**
     * Selenium test case factory
     *
     * @var RemoteDriverFactory
     */
    protected $remoteDriverFactory;

    /**
     * Remote driver instance.
     *
     * @var RemoteDriver
     */
    protected $driver;

    /**
     * Object manager instance.
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Page loader instance.
     *
     * @var PageLoaderInterface
     */
    protected $pageLoader;

    /**
     * @constructor
     * @param DataInterface $configuration
     * @param RemoteDriverFactory $remoteDriverFactory
     * @param EventManagerInterface $eventManager
     * @param ObjectManager $objectManager
     * @param PageLoaderInterface $pageLoader
     */
    public function __construct(
        DataInterface $configuration,
        RemoteDriverFactory $remoteDriverFactory,
        EventManagerInterface $eventManager,
        ObjectManager $objectManager,
        PageLoaderInterface $pageLoader
    ) {
        $this->configuration = $configuration;
        $this->remoteDriverFactory = $remoteDriverFactory;
        $this->eventManager = $eventManager;
        $this->objectManager = $objectManager;
        $this->pageLoader = $pageLoader;

        $this->init();
    }

    /**
     * Destructor.
     *
     * @return void
     */
    public function __destruct()
    {
        if ($this->driver->getSessionId()) {
            $this->driver->stop();
        }
    }

    /**
     * Initial web driver.
     *
     * @return void
     */
    protected function init()
    {
        $this->driver = $this->remoteDriverFactory->create();

        $this->driver->setBrowserUrl('about:blank');
        $params = $this->configuration->get('server/0/item/selenium');
        $this->driver->setupSpecificBrowser($params);
        $this->driver->prepareSession();
        $this->driver->currentWindow()->maximize();
        $this->driver->cookie()->clear();
        $this->driver->refresh();
    }

    /**
     * Get native element by locator.
     *
     * @param Locator $locator
     * @param \PHPUnit_Extensions_Selenium2TestCase_Element $context
     * @param bool $wait
     * @return \PHPUnit_Extensions_Selenium2TestCase_Element
     * @throws \Exception
     */
    protected function findElement(
        Locator $locator,
        \PHPUnit_Extensions_Selenium2TestCase_Element $context = null,
        $wait = true
    ) {
        $context = $context === null
            ? $this->driver
            : $context;

        $criteria = $this->getSearchCriteria($locator);
        $this->pageLoader->setDriver($this->driver)->wait();
        if ($wait) {
            return $this->waitUntil(
                function () use ($context, $criteria) {
                    $element = $context->element($criteria);
                    return $element->displayed() ? $element : null;
                }
            );
        }

        $this->pageLoader->wait();

        return $context->element($criteria);
    }

    /**
     * Get native element by Mtf Element.
     *
     * @param ElementInterface $element
     * @param bool $wait
     * @return null|\PHPUnit_Extensions_Selenium2TestCase_Element
     * @throws \PHPUnit_Extensions_Selenium2TestCase_WebDriverException
     */
    protected function getNativeElement(ElementInterface $element, $wait = true)
    {
        $chainElements = [$element];
        while ($element = $element->getContext()) {
            $chainElements[] = $element;
        }

        $contextElement = null;
        /** @var ElementInterface $context */
        foreach (array_reverse($chainElements) as $chainElement) {
            /** @var ElementInterface $chainElement */
            try {
                // First call "getElement" with $resultElement equal "null" value
                $contextElement = $this->findElement($chainElement->getLocator(), $contextElement, $wait);
            } catch (\PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
                throw new \PHPUnit_Extensions_Selenium2TestCase_WebDriverException(
                    sprintf(
                        'Error occurred on attempt to get element. Message: "%s". Locator: "%s" . Wait: "%s"',
                        $e->getMessage(),
                        $chainElement->getAbsoluteSelector(),
                        $wait
                    )
                );
            }
        }

        return $contextElement;
    }

    /**
     * Get search criteria.
     *
     * @param Locator $locator
     * @return \PHPUnit_Extensions_Selenium2TestCase_ElementCriteria
     */
    public function getSearchCriteria(Locator $locator)
    {
        $criteria = new \PHPUnit_Extensions_Selenium2TestCase_ElementCriteria($locator['using']);
        $criteria->value($locator['value']);

        return $criteria;
    }

    /**
     * Inject Js Error collector.
     *
     * @return void
     */
    public function injectJsErrorCollector()
    {
        $this->driver->execute(
            [
                'script' => 'window.onerror = function(msg, url, line) {
                var errors = {};
                if (localStorage.getItem("errorsHistory")) {
                    errors = JSON.parse(localStorage.getItem("errorsHistory"));
                }
                if (!(window.location.href in errors)) {
                    errors[window.location.href] = [];
                }
                errors[window.location.href].push("error: \'" + msg + "\' " + "file: " + url + " " + "line: " + line);
                localStorage.setItem("errorsHistory", JSON.stringify(errors));
                }',
                'args' => []
            ]
        );
    }

    /**
     * Get js errors.
     *
     * @return string[]
     */
    public function getJsErrors()
    {
        return $this->driver->execute(
            [
                'script' => 'errors = JSON.parse(localStorage.getItem("errorsHistory"));
                localStorage.removeItem("errorsHistory");
                return errors;',
                'args' => []
            ]
        );
    }

    /**
     * Click.
     *
     * @param ElementInterface $element
     * @return void
     */
    public function click(ElementInterface $element)
    {
        $absoluteSelector = $element->getAbsoluteSelector();
        $this->eventManager->dispatchEvent(['click_before'], [__METHOD__, $absoluteSelector]);

        $wrapperElement = $this->getNativeElement($element);
        $this->driver->moveto($wrapperElement);

        $this->tryClick($wrapperElement);

        $this->eventManager->dispatchEvent(['click_after'], [__METHOD__, $absoluteSelector]);
    }

    /**
     * Try to click on element.
     *
     * @param \PHPUnit_Extensions_Selenium2TestCase_Element $element
     * @param int $attempt [optional]
     * @param string $blockedElementSelector [optional]
     * @return void
     */
    private function tryClick(
        \PHPUnit_Extensions_Selenium2TestCase_Element $element,
        $attempt = 0,
        $blockedElementSelector = ''
    ) {
        try {
            $element->click();
        } catch (\PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
            // Define height for scroll to up
            $defineHeight = 100;
            $attempt++;
            // Prepare error message
            $errorMessage = substr($e->getMessage(), 0, strpos($e->getMessage(), 'Command duration'));
            // Find element name
            preg_match('/<(\w+)/', $errorMessage, $matches);
            $elementSelector = isset($matches[1]) ? $matches[1] : '*';
            // Find element selector
            if (preg_match_all('/(([^ ]+)="([^"]+)")/', $errorMessage, $matches)) {
                foreach ($matches[0] as $match) {
                    $elementSelector .= "[{$match}]";
                }
            }

            $js = "var element = jQuery('$elementSelector'),
                       height = $defineHeight;

                   // If count of found elements are less then one
                   if (element.length !== 0) {
                       // If attempt isn't first and previous element selector is equal current need to scroll by parent element
                       if ($attempt && '$blockedElementSelector' == '$elementSelector') {
                            for (var i = 0; i <= $attempt; i++) {
                                element = element.parent();
                            }
                       }
                       height = element.height();
                   }

                   scrollBy(0, -height);
                ";

            $this->driver->execute(['script' => $js, 'args' => []]);
            $this->tryClick($element, $attempt, $elementSelector);
        }
    }

    /**
     * Double click.
     *
     * @param ElementInterface $element
     * @return void
     */
    public function doubleClick(ElementInterface $element)
    {
        $this->eventManager->dispatchEvent(['double_click_before'], [__METHOD__, $element->getAbsoluteSelector()]);

        $this->driver->moveto($this->getNativeElement($element));
        $this->driver->doubleclick();
    }

    /**
     * Right click.
     *
     * @param ElementInterface $element
     * @return void
     */
    public function rightClick(ElementInterface $element)
    {
        $this->eventManager->dispatchEvent(['right_click_before'], [__METHOD__, $element->getAbsoluteSelector()]);

        $this->driver->moveto($this->getNativeElement($element));
        $this->driver->click(\PHPUnit_Extensions_Selenium2TestCase_SessionCommand_Click::RIGHT);
    }

    /**
     * Check whether element is visible.
     *
     * @param ElementInterface $element
     * @return bool
     */
    public function isVisible(ElementInterface $element)
    {
        try {
            $this->eventManager->dispatchEvent(['is_visible'], [__METHOD__, $element->getAbsoluteSelector()]);
            $visible = $this->getNativeElement($element, false)->displayed();
        } catch (\PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
            $visible = false;
        }

        return $visible;
    }

    /**
     * Check whether element is enabled.
     *
     * @param ElementInterface $element
     * @return bool
     */
    public function isDisabled(ElementInterface $element)
    {
        return !$this->getNativeElement($element)->enabled();
    }

    /**
     * Check whether element is selected.
     *
     * @param ElementInterface $element
     * @return bool
     */
    public function isSelected(ElementInterface $element)
    {
        return $this->getNativeElement($element)->selected();
    }

    /**
     * Set the value.
     *
     * @param ElementInterface $element
     * @param string|array $value
     * @return void
     */
    public function setValue(ElementInterface $element, $value)
    {
        $this->eventManager->dispatchEvent(['set_value'], [__METHOD__, $element->getAbsoluteSelector()]);

        $wrappedElement = $this->getNativeElement($element);
        $this->driver->moveto($wrappedElement);
        $wrappedElement->clear();
        $this->focus($element);

        $wrappedElement->value($value);
        $this->triggerChangeEvent($element);
    }

    /**
     * Get the value.
     *
     * @param ElementInterface $element
     * @return null|string
     */
    public function getValue(ElementInterface $element)
    {
        $this->eventManager->dispatchEvent(['get_value'], [__METHOD__, $element->getAbsoluteSelector()]);
        return $this->getNativeElement($element)->value();
    }

    /**
     * Get content.
     *
     * @param ElementInterface $element
     * @return string
     */
    public function getText(ElementInterface $element)
    {
        return $this->getNativeElement($element)->text();
    }

    /**
     * Find element on the page.
     *
     * @param string $selector
     * @param string $strategy
     * @param string $type = select|multiselect|checkbox|null OR custom class with full namespace
     * @param ElementInterface $context
     * @return ElementInterface
     * @throws \Exception
     */
    public function find(
        $selector,
        $strategy = Locator::SELECTOR_CSS,
        $type = null,
        ElementInterface $context = null
    ) {
        $locator = new Locator($selector, $strategy);

        $this->eventManager->dispatchEvent(['find'], [__METHOD__, $locator]);

        $className = 'Magento\Mtf\Client\ElementInterface';
        if (null !== $type) {
            if (strpos($type, '\\') === false) {
                $type = ucfirst(strtolower($type));
                if (class_exists('Magento\Mtf\Client\Element\\' . $type . 'Element')) {
                    $className = 'Magento\Mtf\Client\Element\\' . $type . 'Element';
                }
            } else {
                if (!class_exists($type) && !interface_exists($type)) {
                    throw new \Exception(
                        sprintf('Requested interface or class "%s" does not exists!', $type)
                    );
                }
                $className = $type;
            }
        }

        return $this->objectManager->create(
            $className,
            [
                'driver' => $this,
                'locator' => $locator,
                'context' => $context
            ]
        );
    }

    /**
     * Drag and drop element to(between) another element(s).
     *
     * @param ElementInterface $element
     * @param ElementInterface $target
     * @return void
     */
    public function dragAndDrop(ElementInterface $element, ElementInterface $target)
    {
        $this->driver->moveto($this->getNativeElement($element));
        $this->driver->buttondown();

        $this->driver->moveto($this->getNativeElement($target));
        $this->driver->buttonup();
    }

    /**
     * Hover mouse over an element.
     *
     * @param ElementInterface $element
     * @return void
     */
    public function hover(ElementInterface $element)
    {
        $this->driver->moveto($this->getNativeElement($element));
    }

    /**
     * Send a sequence of key strokes to the active element.
     *
     * @param ElementInterface $element
     * @param array $keys
     * @return void
     */
    public function keys(ElementInterface $element, array $keys)
    {
        $wrappedElement = $this->getNativeElement($element);
        $wrappedElement->clear();
        $this->focus($element);
        foreach ($keys as $key) {
            $this->driver->keys($key);
        }
    }

    /**
     * Wait until callback isn't null or timeout occurs.
     *
     * @param callable $callback
     * @return mixed
     * @throws \Exception
     */
    public function waitUntil($callback)
    {
        return $this->driver->waitUntil($callback);
    }

    /**
     * Get all elements by locator.
     *
     * @param ElementInterface $context
     * @param string $selector
     * @param string $strategy
     * @param null|string $type
     * @param bool $wait
     * @return ElementInterface[]
     * @throws \Exception
     */
    public function getElements(
        ElementInterface $context,
        $selector,
        $strategy = Locator::SELECTOR_CSS,
        $type = null,
        $wait = true
    ) {
        $locator = new Locator($selector, $strategy);
        $criteria = $this->getSearchCriteria($locator);
        $nativeContext = $this->getNativeElement($context);
        $resultElements = [];
        if ($wait) {
            try {
                $nativeElements = $this->waitUntil(
                    function () use ($nativeContext, $criteria) {
                        return $nativeContext->elements($criteria);
                    }
                );
            } catch (\Exception $e) {
                throw new \Exception(
                    sprintf(
                        'Error occurred during waiting for an elements. Message: "%s". Locator: "%s"',
                        $e->getMessage(),
                        $context->getAbsoluteSelector() . ' -> ' . $locator
                    )
                );
            }
        } else {
            $nativeElements = $nativeContext->elements($criteria);
        }

        foreach ($nativeElements as $key => $element) {
            $resultElements[] = $this->find(
                $this->getRelativeXpath($element, $nativeContext),
                Locator::SELECTOR_XPATH,
                $type,
                $context
            );
        }

        return $resultElements;
    }

    /**
     * Retrieve relative xpath from context to element.
     *
     * @param \PHPUnit_Extensions_Selenium2TestCase_Element $element
     * @param \PHPUnit_Extensions_Selenium2TestCase_Element $context
     * @param string $path
     * @param bool $includeLastIndex
     * @return null
     */
    protected function getRelativeXpath(
        \PHPUnit_Extensions_Selenium2TestCase_Element $element,
        \PHPUnit_Extensions_Selenium2TestCase_Element $context,
        $path = '',
        $includeLastIndex = true
    ) {
        if ($element->equals($context)) {
            return '.' . $path;
        }

        $parentLocator = new Locator('..', Locator::SELECTOR_XPATH);
        $parentElement = $element->element($this->getSearchCriteria($parentLocator));

        $childrenLocator = new Locator('*', Locator::SELECTOR_XPATH);

        $index = 1;
        $tag = $element->name();
        if (!$includeLastIndex) {
            return $this->getRelativeXpath($parentElement, $context, '/' . $tag);
        }
        foreach ($parentElement->elements($this->getSearchCriteria($childrenLocator)) as $child) {
            if ($child->equals($element)) {
                return $this->getRelativeXpath($parentElement, $context, '/' . $tag . '[' . $index . ']' . $path);
            }
            if ($child->name() == $tag) {
                ++$index;
            }
        }
        return null;
    }

    /**
     * Get the value of a the given attribute of the element.
     *
     * @param ElementInterface $element
     * @param string $name
     * @return string
     */
    public function getAttribute(ElementInterface $element, $name)
    {
        return $this->getNativeElement($element)->attribute($name);
    }

    /**
     * Open page.
     *
     * @param string $url
     * @return void
     */
    public function open($url)
    {
        $this->eventManager->dispatchEvent(['open_before'], [__METHOD__, $url]);
        $this->driver->url($url);
        $this->eventManager->dispatchEvent(['open_after'], [__METHOD__, $url]);
    }

    /**
     * Back to previous page.
     *
     * @return void
     */
    public function back()
    {
        $this->driver->back();
        $this->eventManager->dispatchEvent(['back'], [__METHOD__]);
    }

    /**
     * Forward page.
     *
     * @return void
     */
    public function forward()
    {
        $this->driver->forward();
        $this->eventManager->dispatchEvent(['forward'], [__METHOD__]);
    }

    /**
     * Refresh page.
     *
     * @return void
     */
    public function refresh()
    {
        $this->driver->refresh();
    }

    /**
     * Reopen browser.
     *
     * @return void
     */
    public function reopen()
    {
        $this->eventManager->dispatchEvent(['reopen'], [__METHOD__]);
        if ($this->driver->getSessionId()) {
            $this->driver->stop();
        }
        if ($sessionStrategy = $this->configuration->get('server/0/item/selenium/sessionStrategy')) {
            $this->driver->setSessionStrategy($sessionStrategy);
        } else {
            $this->driver->setSessionStrategy('isolated');
        }
        $this->init();
    }

    /**
     * Change the focus to a frame in the page by locator.
     *
     * @param Locator|null $locator
     * @return void
     * @throws \Exception
     */
    public function switchToFrame(Locator $locator = null)
    {
        if ($locator) {
            $this->eventManager->dispatchEvent(['switch_to_frame'], [(string)$locator]);
            try {
                $element = $this->findElement($locator);
            } catch (\Exception $e) {
                throw new \Exception(
                    sprintf(
                        'Error occurred during switch to frame! Message: "%s". Locator: "%s".',
                        $e->getMessage(),
                        $locator
                    )
                );
            }
        } else {
            $this->eventManager->dispatchEvent(['switch_to_frame'], ['Switch to main window']);
            $element = null;
        }

        $this->driver->frame($element);
    }

    /**
     * Close the current window.
     *
     * @return void
     */
    public function closeWindow()
    {
        $windowHandles = $this->driver->windowHandles();
        if (count($windowHandles) > 1) {
            $this->driver->window(end($windowHandles));
            $this->driver->closeWindow();
            $this->driver->window(reset($windowHandles));
        } else {
            $this->driver->closeWindow();
        }
    }

    /**
     * Select window by its name.
     *
     * @return void
     */
    public function selectWindow()
    {
        $windowHandles = $this->driver->windowHandles();
        $this->driver->window(end($windowHandles));
    }

    /**
     * Get page title text.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->driver->title();
    }

    /**
     * Press OK on an alert or confirm a dialog.
     *
     * @return void
     */
    public function acceptAlert()
    {
        //$this->_driver->acceptAlert(); Temporary fix for selenium issue 3544
        $this->waitForOperationSuccess('acceptAlert');
        $this->eventManager->dispatchEvent(['accept_alert_after'], [__METHOD__]);
    }

    /**
     * Press Cancel on alert or does not confirm a dialog.
     *
     * @return void
     */
    public function dismissAlert()
    {
        //$this->_driver->dismissAlert(); Temporary fix for selenium issue 3544
        $this->waitForOperationSuccess('dismissAlert');
        $this->eventManager->dispatchEvent(['dismiss_alert_after'], [__METHOD__]);
    }

    /**
     * @todo Temporary fix for selenium issue 3544
     * https://code.google.com/p/selenium/issues/detail?id=3544
     *
     * @param string $operation
     */
    protected function waitForOperationSuccess($operation)
    {
        $driver = $this->driver;
        $this->waitUntil(
            function () use ($driver, $operation) {
                try {
                    $driver->$operation();
                } catch (\PHPUnit_Extensions_Selenium2TestCase_WebDriverException $exception) {
                    return null;
                }
                return true;
            }
        );
    }

    /**
     * Get the alert dialog text.
     *
     * @return string
     */
    public function getAlertText()
    {
        return $this->driver->alertText();
    }

    /**
     * Set the text to a prompt popup.
     *
     * @param string $text
     * @return void
     */
    public function setAlertText($text)
    {
        $this->driver->alertText($text);
    }

    /**
     * Get current page url.
     *
     * @return string
     */
    public function getUrl()
    {
        try {
            if ($this->driver->alertText()) {
                return null;
            }
        } catch (\PHPUnit_Extensions_Selenium2TestCase_WebDriverException $exception) {
            return $this->driver->url();
        }

        return $this->driver->url();
    }

    /**
     * Get Html page source.
     *
     * @return string
     */
    public function getHtmlSource()
    {
        return $this->driver->source();
    }

    /**
     * Get binary string of image.
     *
     * @return string
     */
    public function getScreenshotData()
    {
        return $this->driver->currentScreenshot();
    }

    /**
     * Set focus on element.
     *
     * @param ElementInterface $element
     * @return void
     */
    public function focus(ElementInterface $element)
    {
        $elementId = $element->getAttribute('id');
        if ($elementId) {
            $js = "if (window.jQuery != undefined) jQuery('[id=\"$elementId\"]').focus(); ";
            $js .= "var element = document.getElementById('$elementId'); if (element != undefined) element.focus();";
            $this->driver->execute(['script' => $js, 'args' => []]);
        } else {
            $element->click();
        }
    }

    /**
     * Trigger change on event.
     *
     * @param ElementInterface $element
     * @return void
     */
    protected function triggerChangeEvent(ElementInterface $element)
    {
        $elementId = $element->getAttribute('id');
        if ($elementId) {
            $js = "if (window.jQuery != undefined)";
            $js .= "{jQuery('[id=\"$elementId\"]').change(); jQuery('[id=\"$elementId\"]').keyup();}";
            $js .= "var element = document.getElementById('$elementId'); if (element != undefined) element.focus();";
            $this->driver->execute(['script' => $js, 'args' => []]);
        }
    }
}
