<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity;

use Magento\Framework\App\Utility\Files;

/**
 * PAY ATTENTION: Current implementation does not support of virtual types
 */
class ObserverImplementationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Observer interface
     */
    const OBSERVER_INTERFACE = \Magento\Framework\Event\ObserverInterface::class;

    /**
     * @var array
     */
    protected static $observerClasses = [];

    public static function setUpBeforeClass()
    {
        self::$observerClasses = array_merge(
            self::getObserverClasses('{*/events.xml,events.xml}', '//observer')
        );
    }

    public function testObserverInterfaceImplementation()
    {
        $errors = [];
        foreach (self::$observerClasses as $observerClass) {
            if (!is_subclass_of($observerClass, self::OBSERVER_INTERFACE)) {
                $errors[] = $observerClass;
            }
        }

        if ($errors) {
            $errors = array_unique($errors);
            sort($errors);
            $this->fail(
                sprintf(
                    '%d of observers which not implement \Magento\Framework\Event\ObserverInterface: %s',
                    count($errors),
                    "\n" . implode("\n", $errors)
                )
            );
        }
    }

    public function testObserverHasNoExtraPublicMethods()
    {
        $errors = [];
        foreach (self::$observerClasses as $observerClass) {
            $reflection = (new \ReflectionClass($observerClass));
            $maxCountMethod = $reflection->getConstructor() ? 2 : 1;

            if (count($reflection->getMethods(\ReflectionMethod::IS_PUBLIC)) > $maxCountMethod) {
                $errors[] = $observerClass;
            }
        }

        if ($errors) {
            $errors = array_unique($errors);
            sort($errors);
            $this->fail(
                sprintf(
                    '%d of observers have extra public methods: %s',
                    count($errors),
                    implode("\n", $errors)
                )
            );
        }
    }

    /**
     * @param string $fileNamePattern
     * @param string $xpath
     * @return array
     */
    protected static function getObserverClasses($fileNamePattern, $xpath)
    {
        $observerClasses = [];
        foreach (Files::init()->getConfigFiles($fileNamePattern, [], false) as $configFile) {
            foreach (simplexml_load_file($configFile)->xpath($xpath) as $observer) {
                $className = (string)$observer->attributes()->instance;
                // $className may be empty in cases like this <observer name="observer_name" disabled="true" />
                if ($className) {
                    $observerClasses[] = trim((string)$observer->attributes()->instance, '\\');
                }
            }
        }

        $blacklistFiles = str_replace('\\', '/', realpath(__DIR__)) . '/_files/blacklist/observers*.txt';
        $blacklistExceptions = [];
        foreach (glob($blacklistFiles) as $fileName) {
            $blacklistExceptions = array_merge($blacklistExceptions, file($fileName, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES));
        }
        return array_diff(
            array_unique($observerClasses),
            $blacklistExceptions
        );
    }
}
