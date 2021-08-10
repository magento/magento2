<?php
/**
 * @see       https://github.com/laminas/laminas-soap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-soap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-soap/blob/master/LICENSE.md New BSD License
 */

namespace Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset;

/**
 * Test Class
 */
class PropertyDocumentationTestClass
{
    /**
     * Property documentation
     */
    public $withoutType;

    /**
     * Property documentation
     * @type int
     */
    public $withType;

    public $noDoc;
}
