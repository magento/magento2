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
namespace Magento\PageCache\Model\App\FrontController;

/**
 * Class HeadPlugin
 */
class HeaderPlugin
{
    /**
     * @var \Magento\Core\Model\Layout
     */
    protected $layout;

    /**
     * @var \Magento\Core\Model\ConfigInterface
     */
    protected $config;

    /**
     * Constructor
     *
     * @param \Magento\Core\Model\Layout $layout
     * @param \Magento\Core\Model\ConfigInterface $config
     */
    public function __construct(
        \Magento\Core\Model\Layout $layout,
        \Magento\Core\Model\ConfigInterface $config
    ){
        $this->layout = $layout;
        $this->config = $config;
    }

    /**
     * Modify response after dispatch
     *
     * @param \Magento\App\Response\Http $response
     * @return \Magento\App\Response\Http
     */
    public function afterDispatch(\Magento\App\Response\Http $response)
    {
        $maxAge = $this->config->getValue('system/headers/max-age');
        if ($this->layout->isCacheable()) {
            $response->setHeader('pragma', 'cache', true);
            if($this->layout->isPrivate()) {
                $response->setHeader('cache-control', 'private, max-age=' . $maxAge, true);
                $response->setHeader('expires',
                    gmdate('D, d M Y H:i:s T', strtotime('+' . $maxAge . ' seconds')), true);
            } else {
                $response->setHeader('cache-control', 'public, max-age=' . $maxAge, true);
                $response->setHeader('expires',
                    gmdate('D, d M Y H:i:s T', strtotime('+' . $maxAge . ' seconds')), true);
            }
        } else {
            $response->setHeader('pragma', 'no-cache', true);
            $response->setHeader('cache-control', 'no-store, no-cache, must-revalidate, max-age=0', true);
            $response->setHeader('expires',
                gmdate('D, d M Y H:i:s T', strtotime('-' . $maxAge . ' seconds')), true);
        }
        return $response;
    }
}
