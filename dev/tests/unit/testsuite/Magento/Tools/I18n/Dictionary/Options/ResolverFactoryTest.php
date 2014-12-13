<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tools\I18n\Dictionary\Options;

/**
 * Class ResolverTest
 */
class ResolverFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var \Magento\Tools\I18n\Dictionary\Options\ResolverFactory $resolverFactory */
        $resolverFactory = $objectManagerHelper
            ->getObject('Magento\Tools\I18n\Dictionary\Options\ResolverFactory');
        $this->assertInstanceOf(
            \Magento\Tools\I18n\Dictionary\Options\ResolverFactory::DEFAULT_RESOLVER,
            $resolverFactory->create('some_dir', true)
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage stdClass doesn't implement ResolverInterface
     */
    public function testCreateException()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var \Magento\Tools\I18n\Dictionary\Options\ResolverFactory $resolverFactory */
        $resolverFactory = $objectManagerHelper->getObject(
            'Magento\Tools\I18n\Dictionary\Options\ResolverFactory',
            [
                'resolverClass' => 'stdClass'
            ]
        );
        $resolverFactory->create('some_dir', true);
    }
}
