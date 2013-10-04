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
namespace Magento\Core\Model;

class DirTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $code
     * @param string $value
     * @expectedException \InvalidArgumentException
     * @dataProvider invalidUriDataProvider
     */
    public function testInvalidUri($code, $value)
    {
        new \Magento\Core\Model\Dir(__DIR__, array($code => $value));
    }

    /**
     * @return array
     */
    public function invalidUriDataProvider()
    {
        return array(
            array(\Magento\Core\Model\Dir::MEDIA, '/'),
            array(\Magento\Core\Model\Dir::MEDIA, '//'),
            array(\Magento\Core\Model\Dir::MEDIA, '/value'),
            array(\Magento\Core\Model\Dir::MEDIA, 'value/'),
            array(\Magento\Core\Model\Dir::MEDIA, '/value/'),
            array(\Magento\Core\Model\Dir::MEDIA, 'one\\two'),
            array(\Magento\Core\Model\Dir::MEDIA, '../dir'),
            array(\Magento\Core\Model\Dir::MEDIA, './dir'),
            array(\Magento\Core\Model\Dir::MEDIA, 'one/../two'),
        );
    }

    public function testGetUri()
    {
        $dir = new \Magento\Core\Model\Dir(__DIR__, array(
            \Magento\Core\Model\Dir::PUB   => '',
            \Magento\Core\Model\Dir::MEDIA => 'test',
            'custom' => 'test2'
        ));

        // arbitrary custom value
        $this->assertEquals('test2', $dir->getUri('custom'));

        // setting empty value correctly adjusts its children
        $this->assertEquals('', $dir->getUri(\Magento\Core\Model\Dir::PUB));
        $this->assertEquals('lib', $dir->getUri(\Magento\Core\Model\Dir::PUB_LIB));

        // at the same time if another child has custom value, it must not be affected by its parent
        $this->assertEquals('test', $dir->getUri(\Magento\Core\Model\Dir::MEDIA));
        $this->assertEquals('test/upload', $dir->getUri(\Magento\Core\Model\Dir::UPLOAD));
    }

    /**
     * Test that URIs are not affected by custom dirs
     */
    public function testGetUriIndependentOfDirs()
    {
        $fixtureDirs = array(
            \Magento\Core\Model\Dir::ROOT => __DIR__ . '/root',
            \Magento\Core\Model\Dir::MEDIA => __DIR__ . '/media',
            'custom' => 'test2'
        );
        $default = new \Magento\Core\Model\Dir(__DIR__);
        $custom = new \Magento\Core\Model\Dir(__DIR__, array(), $fixtureDirs);
        foreach (array_keys($fixtureDirs) as $dirCode ) {
            $this->assertEquals($default->getUri($dirCode), $custom->getUri($dirCode));
        }
    }

    public function testGetDir()
    {
        $newRoot = __DIR__ . DIRECTORY_SEPARATOR . 'root';
        $newMedia = __DIR__ . DIRECTORY_SEPARATOR . 'media';
        $dir = new \Magento\Core\Model\Dir(__DIR__, array(), array(
            \Magento\Core\Model\Dir::ROOT => $newRoot,
            \Magento\Core\Model\Dir::MEDIA => $newMedia,
            'custom' => 'test2'
        ));

        // arbitrary custom value
        $this->assertEquals('test2', $dir->getDir('custom'));

        // new root has affected all its non-customized children
        $this->assertStringStartsWith($newRoot, $dir->getDir(\Magento\Core\Model\Dir::APP));
        $this->assertStringStartsWith($newRoot, $dir->getDir(\Magento\Core\Model\Dir::MODULES));

        // but it didn't affect the customized dirs
        $this->assertEquals($newMedia, $dir->getDir(\Magento\Core\Model\Dir::MEDIA));
        $this->assertStringStartsWith($newMedia, $dir->getDir(\Magento\Core\Model\Dir::UPLOAD));
    }

    /**
     * Test that dirs are not affected by custom URIs
     */
    public function testGetDirIndependentOfUris()
    {
        $fixtureUris = array(
            \Magento\Core\Model\Dir::PUB   => '',
            \Magento\Core\Model\Dir::MEDIA => 'test',
            'custom' => 'test2'
        );
        $default = new \Magento\Core\Model\Dir(__DIR__);
        $custom = new \Magento\Core\Model\Dir(__DIR__, $fixtureUris);
        foreach (array_keys($fixtureUris) as $dirCode ) {
            $this->assertEquals($default->getDir($dirCode), $custom->getDir($dirCode));
        }
    }
}
