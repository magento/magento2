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
namespace Magento\Catalog\Service\V1\Product\Attribute\Media;

use \Magento\Framework\App\Filesystem;

class WriteServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $contentValidatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mediaConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $productLoaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $entryResolverMock;

    /**
     * @var WriteService
     */
    private $service;

    protected function setUp()
    {
        $this->contentValidatorMock = $this->getMock(
            'Magento\Catalog\Service\V1\Product\Attribute\Media\Data\GalleryEntryContentValidator',
            array(),
            array(),
            '',
            false
        );
        $this->filesystemMock = $this->getMock(
            'Magento\Framework\App\Filesystem',
            array(),
            array(),
            '',
            false
        );
        $this->mediaConfigMock = $this->getMock(
            'Magento\Catalog\Model\Product\Media\Config',
            array(),
            array(),
            '',
            false
        );
        $this->productLoaderMock = $this->getMock(
            'Magento\Catalog\Service\V1\Product\ProductLoader',
            array(),
            array(),
            '',
            false
        );
        $this->storeFactoryMock = $this->getMock(
            'Magento\Store\Model\StoreFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->entryResolverMock = $this->getMock(
            'Magento\Catalog\Service\V1\Product\Attribute\Media\GalleryEntryResolver',
            array(),
            array(),
            '',
            false
        );

        $this->service = new WriteService(
            $this->contentValidatorMock,
            $this->filesystemMock,
            $this->productLoaderMock,
            $this->mediaConfigMock,
            $this->storeFactoryMock,
            $this->entryResolverMock
        );
    }

    public function testCreate()
    {
        $productSku = 'simple';
        $storeId = 1;
        $mediaTmpPath = 'tmp';
        $entry = array(
            'disabled' => true,
            'types' => array('image'),
            'label' => 'Image',
            'position' => 100,
        );
        $entryContent = array(
            'name' => 'image',
            'mime_type' => 'image/jpg',
            'data' => base64_encode('image_content'),
        );

        $storeMock = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);
        $storeMock->expects($this->any())->method('getId')->will($this->returnValue($storeId));
        $storeMock->expects($this->any())->method('load')->will($this->returnSelf());
        $this->storeFactoryMock->expects($this->once())->method('create')->will($this->returnValue($storeMock));
        $entryMock = $this->getMock(
            'Magento\Catalog\Service\V1\Product\Attribute\Media\Data\GalleryEntry',
            array(),
            array(),
            '',
            false
        );
        $entryContentMock = $this->getMock(
            'Magento\Catalog\Service\V1\Product\Attribute\Media\Data\GalleryEntryContent',
            array(),
            array(),
            '',
            false
        );
        $this->contentValidatorMock->expects($this->once())->method('isValid')->with($entryContentMock)
            ->will($this->returnValue(true));
        $productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $this->productLoaderMock->expects($this->once())->method('load')->with($productSku)
            ->will($this->returnValue($productMock));
        $this->mediaConfigMock->expects($this->any())->method('getBaseTmpMediaPath')
            ->will($this->returnValue($mediaTmpPath));
        $mediaDirectoryMock = $this->getMock('Magento\Framework\Filesystem\Directory\WriteInterface');
        $this->filesystemMock->expects($this->any())->method('getDirectoryWrite')->with(Filesystem::MEDIA_DIR)
            ->will($this->returnValue($mediaDirectoryMock));

        $mediaDirectoryMock->expects($this->once())->method('create')->with($mediaTmpPath);
        $mediaDirectoryMock->expects($this->once())->method('delete')->with('tmp' . DIRECTORY_SEPARATOR . 'image.jpg');
        $mediaDirectoryMock->expects($this->any())->method('getAbsolutePath')
            ->with('tmp' . DIRECTORY_SEPARATOR . 'image.jpg')
            ->will($this->returnValue('/i/m/image.jpg'));

        $mediaDirectoryMock->expects($this->once())->method('writeFile')
            ->with('tmp' . DIRECTORY_SEPARATOR . 'image.jpg', 'image_content');

        $entryContentMock->expects($this->any())->method('getData')->will($this->returnValue($entryContent['data']));
        $entryContentMock->expects($this->any())->method('getName')->will($this->returnValue($entryContent['name']));
        $entryContentMock->expects($this->any())->method('getMimeType')->will($this->returnValue(
            $entryContent['mime_type']
        ));

        $entryMock->expects($this->any())->method('isDisabled')->will($this->returnValue($entry['disabled']));
        $entryMock->expects($this->any())->method('getTypes')->will($this->returnValue($entry['types']));
        $entryMock->expects($this->any())->method('getLabel')->will($this->returnValue($entry['label']));
        $entryMock->expects($this->any())->method('getPosition')->will($this->returnValue($entry['position']));

        $galleryMock = $this->getGalleryAttributeBackendMock($productMock);
        $testImageUri = '/i/m/image2.jpg';
        $galleryMock->expects($this->once())->method('addImage')->with(
            $productMock,
            '/i/m/image.jpg',
            $entry['types'],
            true,
            $entry['disabled']
        )->will($this->returnValue($testImageUri));

        $galleryMock->expects($this->once())->method('updateImage')->with(
            $productMock,
            $testImageUri,
            array(
                'label' => $entry['label'],
                'position' => $entry['position'],
                'disabled' => $entry['disabled'],
            )
        );
        $productMock->expects($this->once())->method('save');
        $galleryMock->expects($this->once())->method('getRenamedImage')->with($testImageUri)->will(
            $this->returnValue($testImageUri)
        );
        $entryId = 1;
        $this->entryResolverMock->expects($this->once())->method('getEntryIdByFilePath')
            ->with($productMock, $testImageUri)
            ->will($this->returnValue($entryId));
        $this->assertEquals($entryId, $this->service->create($productSku, $entryMock, $entryContentMock, $storeId));
    }

    public function testUpdate()
    {
        $productSku = 'simple';
        $storeId = 1;
        $entry = array(
            'id' => 1,
            'disabled' => true,
            'types' => array('image'),
            'label' => 'Updated Image',
            'position' => 100,
        );
        $storeMock = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);
        $storeMock->expects($this->any())->method('getId')->will($this->returnValue($storeId));
        $storeMock->expects($this->any())->method('load')->will($this->returnSelf());
        $this->storeFactoryMock->expects($this->once())->method('create')->will($this->returnValue($storeMock));
        $entryMock = $this->getMock(
            'Magento\Catalog\Service\V1\Product\Attribute\Media\Data\GalleryEntry',
            array(),
            array(),
            '',
            false
        );

        $productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $this->productLoaderMock->expects($this->once())->method('load')->with($productSku)
            ->will($this->returnValue($productMock));

        $entryMock->expects($this->any())->method('getId')->will($this->returnValue($entry['id']));
        $entryMock->expects($this->any())->method('isDisabled')->will($this->returnValue($entry['disabled']));
        $entryMock->expects($this->any())->method('getTypes')->will($this->returnValue($entry['types']));
        $entryMock->expects($this->any())->method('getLabel')->will($this->returnValue($entry['label']));
        $entryMock->expects($this->any())->method('getPosition')->will($this->returnValue($entry['position']));
        $galleryMock = $this->getGalleryAttributeBackendMock($productMock);

        $testImageUri = '/i/m/image2.jpg';
        $mediaAttributes = array('image' => 'image', 'small_image' => 'small_image', 'thumbnail' => 'thumbnail');
        $productMock->expects($this->any())->method('getMediaAttributes')->will($this->returnValue($mediaAttributes));
        $this->entryResolverMock->expects($this->once())->method('getEntryFilePathById')
            ->with($productMock, $entry['id'])
            ->will($this->returnValue($testImageUri));
        $galleryMock->expects($this->once())->method('updateImage')->with(
            $productMock,
            $testImageUri,
            array(
                'label' => $entry['label'],
                'position' => $entry['position'],
                'disabled' => $entry['disabled'],
            )
        );
        $galleryMock->expects($this->once())->method('clearMediaAttribute')
            ->with($productMock, array('image', 'small_image', 'thumbnail'));
        $galleryMock->expects($this->once())->method('setMediaAttribute')
            ->with($productMock, $entry['types'], $testImageUri);
        $productMock->expects($this->once())->method('save');

        $this->assertTrue($this->service->update($productSku, $entryMock, $storeId));
    }

    public function testDelete()
    {
        $productSku = 'simple';
        $storeId = 1;
        $entryId = 1;
        $productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $this->productLoaderMock->expects($this->once())->method('load')->with($productSku)
            ->will($this->returnValue($productMock));

        $galleryMock = $this->getGalleryAttributeBackendMock($productMock);

        $testImageUri = '/i/m/image2.jpg';

        $this->entryResolverMock->expects($this->once())->method('getEntryFilePathById')
            ->with($productMock, $entryId)
            ->will($this->returnValue($testImageUri));
        $galleryMock->expects($this->once())->method('removeImage')->with($productMock, $testImageUri);
        $productMock->expects($this->once())->method('save');

        $this->assertTrue($this->service->delete($productSku, $entryId, $storeId));
    }

    /**
     * Create mock for media gallery attribute backend model
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $productMock
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getGalleryAttributeBackendMock($productMock)
    {
        $typeInstanceMock = $this->getMock(
            'Magento\Catalog\Model\Product\Type\Simple',
            array(),
            array(),
            '',
            false
        );
        $attributeModelMock = $this->getMockForAbstractClass('Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
            array(), '', false, false, true, array('getBackend', '__wakeup'));
        $productMock->expects($this->any())->method('getTypeInstance')->will($this->returnValue($typeInstanceMock));
        $typeInstanceMock->expects($this->any())->method('getSetAttributes')->with($productMock)->will(
            $this->returnValue(array(
                'media_gallery' => $attributeModelMock,
            ))
        );
        $backendModelMock = $this->getMock('Magento\Catalog\Model\Product\Attribute\Backend\Media', array(), array(),
            '', false);
        $attributeModelMock->expects($this->any())->method('getBackend')->will($this->returnValue($backendModelMock));
        return $backendModelMock;
    }
}
