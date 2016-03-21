<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Theme;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Theme\Model\Theme\File
     */
    protected $_model;

    /**
     * @var array
     */
    protected $_data = [];

    /**
     * @var \Magento\Theme\Model\Theme
     */
    protected $_theme;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $objectManager->create('Magento\Theme\Model\Theme\File');
        /** @var $themeModel \Magento\Framework\View\Design\ThemeInterface */
        $themeModel = $objectManager->create('Magento\Framework\View\Design\ThemeInterface');
        $this->_theme = $themeModel->getCollection()->getFirstItem();
        $this->_data = [
            'file_path' => 'main.css',
            'file_type' => 'css',
            'content' => 'content files',
            'order' => 0,
            'theme' => $this->_theme,
            'theme_id' => $this->_theme->getId(),
        ];
    }

    protected function tearDown()
    {
        $this->_model = null;
        $this->_data = [];
        $this->_theme = null;
    }

    /**
     * Test crud operations for theme files model using valid data
     */
    public function testCrud()
    {
        $this->_model->setData($this->_data);

        $crud = new \Magento\TestFramework\Entity($this->_model, ['file_path' => 'rename.css']);
        $crud->testCrud();
    }
}
