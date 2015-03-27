<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Section\Config;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Model\Section\Config\Converter */
    protected $converter;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \DOMDocument */
    protected $source;

    protected function setUp()
    {
        $this->source = new \DOMDocument();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->converter = $this->objectManagerHelper->getObject('Magento\Customer\Model\Section\Config\Converter');
    }

    public function testConvert()
    {
        $this->source->loadXML(file_get_contents(__DIR__ . '/_files/sections.xml'));

        $this->assertEquals(
            [
                'sections' => [
                    'customer/account/logout' => '*',
                    'customer/account/editPost' => ['account'],
                ]
            ],
            $this->converter->convert($this->source)
        );
    }
}
