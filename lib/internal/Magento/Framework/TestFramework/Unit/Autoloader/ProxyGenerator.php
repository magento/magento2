<?php

/**
 *  Copyright © Magento, Inc. All rights reserved.
 *  See COPYING.txt for license details.
 */

namespace Magento\Framework\TestFramework\Unit\Autoloader;

use Magento\Framework\TestFramework\Unit\Helper\ProxyClassGenerator;

/**
 * Generates a simple factory class with create() method
 */
class ProxyGenerator implements GeneratorInterface
{
    /**
     * Generates a Proxy class if it follows "<SourceClass>\Proxy" convention
     *
     * @param string $className
     * @return bool|string
     */
    public function generate($className)
    {
        if(!$this->isProxy($className))
        {
            return false;
        }

        $sourceClassName = str_replace("\Proxy","",$className);
        $generate = new ProxyClassGenerator($sourceClassName, $className);
        return $generate->generate();
    }

    /**
     * Check if the class name is a proxy class "<SourceClass>\Proxy"
     *
     * @param string $className
     * @return bool
     */
    private function isProxy($className)
    {
        //if \Proxy not exist
        if ($className === null || !str_contains($className, '\Proxy') ) {
            return false;
        }
        // if \Proxy not exist in last
        $classLength = strlen($className);
        $proxyLength = strlen('\Proxy');
        if (($classLength - $proxyLength) != strpos($className,"\Proxy")){
            return false;
        }
        return true;
    }
}
