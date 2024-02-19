<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Listener of PHPUnit built-in events
 */
namespace Magento\TestFramework\Event;

use ReflectionMethod;
use PHPUnit\Event\Test\PreparationStartedSubscriber;

final class PhpUnitExtensionSubsscriber implements PreparationStartedSubscriber
{
    public function notify(\PHPUnit\Event\Test\PreparationStarted $event): void{
        $className = $event->test()->className();
        $methodName = $event->test()->methodName();
        $docComment = (new ReflectionMethod($className, $methodName))->getDocComment();

        $docComment = str_replace("\r\n", "\n", $docComment);
        $docComment = preg_replace('/\n\s*\*\s?/', "\n", $docComment);
        $docComment = substr($docComment, 0, -1);
        $docComment = rtrim($docComment, "\n");

        if (!preg_match('/@magentoDataFixture\s+/', $docComment, $matches, PREG_OFFSET_CAPTURE)) {
            return;
        }

        $offset            = strlen($matches[0][0]) + (int) $matches[0][1];
        $annotationContent = substr($docComment, $offset);
        $data              = [];

        require $annotationContent;
    }
}
