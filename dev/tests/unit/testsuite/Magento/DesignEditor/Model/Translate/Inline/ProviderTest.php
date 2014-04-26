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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\DesignEditor\Model\Translate\Inline;

class ProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\DesignEditor\Model\Translate\Inline
     */
    protected $translateVde;

    /**
     * @var \Magento\Framework\Translate\Inline
     */
    protected $translateInline;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    protected function setUp()
    {
        $this->translateVde = $this->getMock('Magento\DesignEditor\Model\Translate\Inline', [], [], '', false);
        $this->translateInline = $this->getMock('Magento\Framework\Translate\Inline', [], [], '', false);
        $this->request = $this->getMock('Magento\Framework\App\RequestInterface', [], [], '', false);
    }

    /**
     * @param bool $isVde
     * @param string $instanceName
     *
     * @dataProvider dataProviderGet
     */
    public function testGet($isVde, $instanceName)
    {
        $helper = $this->getMock('Magento\DesignEditor\Helper\Data', [], [], '', false);
        $helper->expects($this->once())
            ->method('isVdeRequest')
            ->will($this->returnValue($isVde));

        $provider = new \Magento\DesignEditor\Model\Translate\Inline\Provider(
            $this->translateVde,
            $this->translateInline,
            $helper,
            $this->request
        );

        $this->assertInstanceOf($instanceName, $provider->get());
    }

    /**
     * @return array
     */
    public function dataProviderGet()
    {
        return [
            [false, 'Magento\Framework\Translate\Inline'],
            [true, 'Magento\DesignEditor\Model\Translate\Inline'],
        ];
    }
}
