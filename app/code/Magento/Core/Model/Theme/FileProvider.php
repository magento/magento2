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
namespace Magento\Core\Model\Theme;

class FileProvider implements \Magento\Framework\View\Design\Theme\FileProviderInterface
{
    /**
     * @var \Magento\Core\Model\Resource\Theme\File\CollectionFactory
     */
    protected $fileFactory;

    /**
     * @param \Magento\Core\Model\Resource\Theme\File\CollectionFactory $fileFactory
     */
    public function __construct(\Magento\Core\Model\Resource\Theme\File\CollectionFactory $fileFactory)
    {
        $this->fileFactory = $fileFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(\Magento\Framework\View\Design\ThemeInterface $theme, array $filters = array())
    {
        /** @var \Magento\Framework\View\Design\Theme\File\CollectionInterface $themeFiles */
        $themeFiles = $this->fileFactory->create();
        $themeFiles->addThemeFilter($theme);
        foreach ($filters as $field => $value) {
            $themeFiles->addFieldToFilter($field, $value);
        }
        $themeFiles->setDefaultOrder();
        return $themeFiles->getItems();
    }
}
