<?php
/**
 * @see       https://github.com/laminas/laminas-soap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-soap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-soap/blob/master/LICENSE.md New BSD License
 */

namespace Magento\Webapi\Model\Laminas\Soap\DocumentationStrategy;

use Magento\Webapi\Api\Data\DocumentationStrategyInterface;
use ReflectionClass;
use ReflectionProperty;

final class ReflectionDocumentation implements DocumentationStrategyInterface
{
    /**
     * @inheirtDoc
     */
    public function getPropertyDocumentation(ReflectionProperty $property)
    {
        return $this->parseDocComment($property->getDocComment());
    }

    /**
     * @inheirtDoc
     */
    public function getComplexTypeDocumentation(ReflectionClass $class)
    {
        return $this->parseDocComment($class->getDocComment());
    }

    /**
     * @param string $docComment
     *
     * @return string
     */
    private function parseDocComment(string $docComment)
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

            // only include newlines if we've already got documentation
            if (! empty($documentation) || $line != '') {
                $documentation[] = $line;
            }
        }

        return join("\n", $documentation);
    }
}
