<?php

declare(strict_types=1);

namespace HelloWorld\HelloWorldPlugins\Plugin;

use HelloWorld\HelloWorld\Model\HelloWorldManagement;

/**
 * Plugin to make rest api result as 'prefix<h1>Hello World</h1>suffix'
 */
class HelloWorldPlugin
{
    /**
     * Add prefix to 'Hello World' string
     *
     * @param HelloWorldManagement $subject
     */
    public function beforeGetHelloWorld(HelloWorldManagement $subject)
    {
        $subject->prefix = "prefix";
    }

    /**
     * Wraps 'Hello World' string with H1 tag.
     *
     * @param HelloWorldManagement $subject
     * @param callable $proceed
     * @return string
     */
    public function aroundGetHelloWorld(HelloWorldManagement $subject, callable $proceed) : string
    {
        $result = $proceed();
        return $subject->prefix . '<h1>' . $result . '</h1>';
    }

    /**
     * Add suffix to 'prefix<h1>Hello World</h1>' string.
     *
     * @param HelloWorldManagement $subject
     * @param string $result
     * @return string
     */
    public function afterGetHelloWorld(HelloWorldManagement $subject, string $result) : string
    {
        return $result . "suffix";
    }
}
