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
namespace Magento\Framework\View\Page\Config\Generator;

use Magento\Framework\View\Layout;
use Magento\Framework\View\Page\Config\Structure;

class Body implements Layout\GeneratorInterface
{
    /**
     * Type of generator
     */
    const TYPE = 'body';

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Page\Config $pageConfig
     */
    public function __construct(\Magento\Framework\View\Page\Config $pageConfig)
    {
        $this->pageConfig = $pageConfig;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     *
     * @param Layout\Reader\Context $readerContext
     * @param Layout\Generator\Context $generatorContext
     * @return $this
     */
    public function process(Layout\Reader\Context $readerContext, Layout\Generator\Context $generatorContext)
    {
        $structure = $readerContext->getPageConfigStructure();
        $this->processBodyClasses($structure);
        return $this;
    }

    /**
     * Process body classes, add to page configuration from scheduled structure
     *
     * @param \Magento\Framework\View\Page\Config\Structure $pageStructure
     * @return $this
     */
    protected function processBodyClasses(Structure $pageStructure)
    {
        foreach ($pageStructure->getBodyClasses() as $class) {
            $this->pageConfig->addBodyClass($class);
        }
        return $this;
    }
}
