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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\View\Asset\ModuleNotation;

class ResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Asset\File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $asset;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetRepo;
    
    /**
     * @var \Magento\Framework\View\Asset\ModuleNotation\Resolver;
     */
    private $object;

    protected function setUp()
    {
        $this->asset = $this->getMock('Magento\Framework\View\Asset\File', array(), array(), '', false);
        $this->assetRepo = $this->getMock('Magento\Framework\View\Asset\Repository', array(), array(), '', false);
        $this->object = new \Magento\Framework\View\Asset\ModuleNotation\Resolver($this->assetRepo);
    }

    public function testConvertModuleNotationToPathNoModularSeparator()
    {
        $this->asset->expects($this->never())->method('getPath');
        $this->assetRepo->expects($this->never())->method('createUsingContext');
        $textNoSeparator = 'name_without_double_colon.ext';
        $this->assertEquals(
            $textNoSeparator,
            $this->object->convertModuleNotationToPath($this->asset, $textNoSeparator)
        );
    }

    /**
     * @param string $assetRelPath
     * @param string $relatedFieldId
     * @param string $similarRelPath
     * @param string $expectedResult
     * @dataProvider convertModuleNotationToPathModularSeparatorDataProvider
     */
    public function testConvertModuleNotationToPathModularSeparator(
        $assetRelPath, $relatedFieldId, $similarRelPath, $expectedResult
    ) {
        $similarAsset = $this->getMock('Magento\Framework\View\Asset\File', array(), array(), '', false);
        $similarAsset->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue($similarRelPath));
        $this->asset->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue($assetRelPath));
        $this->assetRepo->expects($this->once())
            ->method('createSimilar')
            ->with($relatedFieldId, $this->asset)
            ->will($this->returnValue($similarAsset));
        $this->assertEquals(
            $expectedResult,
            $this->object->convertModuleNotationToPath($this->asset, $relatedFieldId)
        );
    }

    /**
     * @return array
     */
    public function convertModuleNotationToPathModularSeparatorDataProvider()
    {
        return array(
            'same module' => array(
                'area/theme/locale/Foo_Bar/styles/style.css',
                'Foo_Bar::images/logo.gif',
                'area/theme/locale/Foo_Bar/images/logo.gif',
                '../images/logo.gif'
            ),
            'non-modular refers to modular' => array(
                'area/theme/locale/css/admin.css',
                'Bar_Baz::images/logo.gif',
                'area/theme/locale/Bar_Baz/images/logo.gif',
                '../Bar_Baz/images/logo.gif'
            ),
            'different modules' => array(
                'area/theme/locale/Foo_Bar/styles/style.css',
                'Bar_Baz::images/logo.gif',
                'area/theme/locale/Bar_Baz/images/logo.gif',
                '../../Bar_Baz/images/logo.gif'
            )
        );
    }
}
