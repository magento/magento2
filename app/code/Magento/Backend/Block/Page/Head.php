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
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml header block
 *
 * @category   Magento
 * @package    Magento_Backend
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backend\Block\Page;

class Head extends \Magento\Theme\Block\Html\Head
{
    /**
     * @var string
     */
    protected $_template = 'page/head.phtml';

    /**
     * @var \Magento\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Core\Helper\File\Storage\Database $fileStorageDatabase
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\View\Asset\GroupedCollection $assets
     * @param \Magento\View\Asset\MergeService $assetMergeService
     * @param \Magento\View\Asset\MinifyService $assetMinifyService
     * @param \Magento\Locale\ResolverInterface $localeResolver
     * @param \Magento\App\Action\Title $titles
     * @param \Magento\Data\Form\FormKey $formKey
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Core\Helper\File\Storage\Database $fileStorageDatabase,
        \Magento\ObjectManager $objectManager,
        \Magento\View\Asset\GroupedCollection $assets,
        \Magento\View\Asset\MergeService $assetMergeService,
        \Magento\View\Asset\MinifyService $assetMinifyService,
        \Magento\Locale\ResolverInterface $localeResolver,
        \Magento\App\Action\Title $titles,
        \Magento\Data\Form\FormKey $formKey,
        array $data = array()
    ) {
        $this->_titles = $titles;
        $this->formKey = $formKey;
        parent::__construct(
            $context,
            $fileStorageDatabase,
            $objectManager,
            $assets,
            $assetMergeService,
            $assetMinifyService,
            $localeResolver,
            $data
        );
        $this->formKey = $formKey;
    }

    /**
     * Retrieve Session Form Key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @return array|string
     */
    public function getTitle()
    {
        /** Get default title */
        $title = parent::getTitle();

        /** Add default title */
        $this->_titles->add($title, true);

        /** Set title list */
        $this->setTitle(array_reverse($this->_titles->get()));

        /** Render titles */
        return parent::getTitle();
    }
}
