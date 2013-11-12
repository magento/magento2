<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Integrity\Modular;

class CacheFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $area
     * @dataProvider cacheConfigDataProvider
     */
    public function testCacheConfig($area)
    {
        $validationStateMock = $this->getMock('Magento\Config\ValidationStateInterface');
        $validationStateMock->expects($this->any())->method('isValidated')->will($this->returnValue(true));

        /** @var \Magento\Module\Dir\Reader $moduleReader */
        $moduleReader = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Module\Dir\Reader');
        $schema = $moduleReader->getModuleDir('etc', 'Magento_Core') . DIRECTORY_SEPARATOR . 'cache.xsd';

        /** @var \Magento\Core\Model\Cache\Config\Reader $reader */
        $reader = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Core\Model\Cache\Config\Reader',
            array(
                'validationState' => $validationStateMock,
                'schema' => $schema,
                'perFileSchema' => $schema,
            )
        );
        try {
            $reader->read($area);
        } catch (\Magento\Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    public function cacheConfigDataProvider()
    {
        return array(
            'global'    => array('global'),
            'adminhtml' =>array('adminhtml'),
            'frontend'  =>array('frontend'),
        );
    }
}
