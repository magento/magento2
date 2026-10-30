<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Mvc\View\Http;

use Magento\Framework\Setup\Mvc\MvcEvent;

/**
 * Native InjectTemplateListener for HTTP request (replaces Laminas dependency)
 *
 * @deprecated Web Setup support has been removed, this class is no longer in use.
 * @see we don't use it anymore
 */
class InjectTemplateListener
{
    /**
     * Determine the top-level namespace of the controller
     *
     * @param  string $controller
     * @return string
     */
    protected function deriveModuleNamespace($controller): string
    {
        if (!strstr($controller, '\\')) {
            return '';
        }

        // Retrieve the first two elemenents representing the vendor and module name.
        $nsArray = explode('\\', $controller);
        $subNsArray = array_slice($nsArray, 0, 2);
        return implode('/', $subNsArray);
    }

    /**
     * Get controller sub-namespace
     *
     * @param string $namespace
     * @return string
     */
    protected function deriveControllerSubNamespace($namespace): string
    {
        if (!strstr($namespace, '\\')) {
            return '';
        }
        $nsArray = explode('\\', $namespace);

        // Remove the first three elements representing the vendor, module name and controller directory.
        $subNsArray = array_slice($nsArray, 3);
        if (empty($subNsArray)) {
            return '';
        }
        return implode('/', $subNsArray);
    }

    /**
     * Inject a template into the view model, if none present
     *
     * Template is derived from the controller found in the route match, and,
     * optionally, the action, if present.
     *
     * @param  MvcEvent $e
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    // phpcs:disable
    public function injectTemplate(MvcEvent $e)
    {
        // Native implementation - simplified for setup context
        // In setup context, we don't need complex template injection
        // This method exists for API compatibility
    }
    // phpcs:disable
}
