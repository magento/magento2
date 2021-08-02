<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Model\Soap\Wsdl\ComplexTypeStrategy;

use ReflectionClass;
use ReflectionProperty;
use Magento\Webapi\Api\Data\ComplexTypeStrategy\DocumentationStrategyInterface;

/**
 * Class ReflectionDocumentation
 */
final class ReflectionDocumentation implements DocumentationStrategyInterface
{
    /**
     * @inheritDoc
     */
    public function getPropertyDocumentation(ReflectionProperty $property): string
    {
        return $this->parseDocComment($property->getDocComment());
    }

    /**
     * @inheritDoc
     */
    public function getComplexTypeDocumentation(ReflectionClass $class): string
    {
        return $this->parseDocComment($class->getDocComment());
    }

    /**
     * This Method parse doc comment.
     *
     * @param string $docComment
     *
     * @return string
     */
    private function parseDocComment(string $docComment): string
    {
        $documentation = [];

        foreach (explode("\n", $docComment) as $i => $line) {
            if ($i == 0) {
                continue;
            }

            $line = trim(preg_replace('/\s*\*+/', '', $line));

            if (preg_match('/^(@[a-z]|\/)/i', $line)) {
                break;
            }

            if (!empty($documentation) || $line != '') {
                $documentation[] = $line;
            }
        }

        return join("\n", $documentation);
    }
}
