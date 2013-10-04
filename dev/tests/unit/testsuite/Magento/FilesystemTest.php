<?php
/**
 * Unit Test for \Magento\Filesystem
 *
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
 *
 */
namespace Magento;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class FilesystemTest extends \PHPUnit_Framework_TestCase
{
    public function testSetWorkingDirectory()
    {
        $filesystem = new \Magento\Filesystem($this->_getDefaultAdapterMock());
        $filesystem->setWorkingDirectory('/tmp');
        $this->assertEquals('/tmp', $filesystem->getWorkingDirectory());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @exceptedExceptionMessage Working directory "/tmp" does not exists
     */
    public function testSetWorkingDirectoryException()
    {
        $adapterMock = $this->getMockBuilder('Magento\Filesystem\AdapterInterface')
            ->getMock();
        $adapterMock->expects($this->once())
            ->method('isDirectory')
            ->with('/tmp')
            ->will($this->returnValue(false));
        $filesystem = new \Magento\Filesystem($adapterMock);
        $filesystem->setWorkingDirectory('/tmp');
    }

    /**
     * @dataProvider allowCreateDirectoriesDataProvider
     * @param bool $allow
     * @param int $mode
     */
    public function testSetIsAllowCreateDirectories($allow, $mode)
    {
        $adapterMock = $this->getMockBuilder('Magento\Filesystem\AdapterInterface')
            ->getMock();
        $filesystem = new \Magento\Filesystem($adapterMock);
        $this->assertSame($filesystem, $filesystem->setIsAllowCreateDirectories($allow, $mode));
        $this->assertAttributeEquals($allow, '_isAllowCreateDirs', $filesystem);
        if (!$mode) {
            $mode = 0777;
        }
        $this->assertAttributeEquals($mode, '_newDirPermissions', $filesystem);
    }

    /**
     * @return array
     */
    public function allowCreateDirectoriesDataProvider()
    {
        return array(
            array(true, 0644),
            array(false, null)
        );
    }

    /**
     * @dataProvider twoFilesOperationsValidDataProvider
     *
     * @param string $method
     * @param string $checkMethod
     * @param string $source
     * @param string $target
     * @param string|null $workingDirectory
     * @param string|null $targetDir
     */
    public function testTwoFilesOperation($method, $checkMethod, $source, $target, $workingDirectory = null,
        $targetDir = null
    ) {
        $adapterMock = $this->getMockBuilder('Magento\Filesystem\AdapterInterface')
            ->getMock();
        $adapterMock->expects($this->exactly(2))
            ->method('isDirectory')
            ->will($this->returnValue(true));
        $adapterMock->expects($this->once())
            ->method($checkMethod)
            ->will($this->returnValue(true));
        $adapterMock->expects($this->once())
            ->method($method)
            ->with($source, $target);

        $filesystem = new \Magento\Filesystem($adapterMock);
        $filesystem->setWorkingDirectory('/tmp');
        $filesystem->$method($source, $target, $workingDirectory, $targetDir);
    }

    /**
     * @return array
     */
    public function twoFilesOperationsValidDataProvider()
    {
        return array(
            'copy both tmp' => array('copy', 'isFile', '/tmp/path/file001.log', '/tmp/path/file001.bak'),
            'move both tmp' => array('rename', 'exists', '/tmp/path/file001.log', '/tmp/path/file001.bak'),
            'copy both tmp #2' => array('copy', 'isFile', '/tmp/path/file001.log', '/tmp/path/file001.bak', '/tmp'),
            'move both tmp #2' => array('rename', 'exists', '/tmp/path/file001.log', '/tmp/path/file001.bak', '/tmp'),
            'copy different'
                => array('copy', 'isFile', '/tmp/path/file001.log', '/storage/file001.bak', null, '/storage'),
            'move different'
                => array('rename', 'exists', '/tmp/path/file001.log', '/storage/file001.bak', null, '/storage'),
            'copy different #2'
                => array('copy', 'isFile', '/tmp/path/file001.log', '/storage/file001.bak', '/tmp', '/storage'),
            'move different #2'
                => array('rename', 'exists', '/tmp/path/file001.log', '/storage/file001.bak', '/tmp', '/storage'),
        );
    }

    /**
     * @dataProvider twoFilesOperationsInvalidDataProvider
     * @param string $method
     * @param string $source
     * @param string $destination
     * @param string $exceptionMessage
     * @param string|null $workingDirectory
     * @param string|null $targetDir
     */
    public function testTwoFilesOperationsIsolationException(
        $method, $source, $destination, $exceptionMessage, $workingDirectory = null, $targetDir = null
    ) {
        $adapterMock = $this->_getDefaultAdapterMock();
        $adapterMock->expects($this->never())
            ->method($method);

        $filesystem = new \Magento\Filesystem($adapterMock);
        $filesystem->setWorkingDirectory('/tmp');


        $this->setExpectedException('InvalidArgumentException', $exceptionMessage);
        $filesystem->$method($source, $destination, $workingDirectory, $targetDir);
    }

    /**
     * @return array
     */
    public function twoFilesOperationsInvalidDataProvider()
    {
        return array(
            'copy first path invalid' => array(
                'copy',
                '/tmp/../etc/passwd',
                '/tmp/path001',
                "Path '/tmp/../etc/passwd' is out of working directory '/tmp'",
            ),
            'copy first path invalid #2' => array(
                'copy',
                '/tmp/../etc/passwd',
                '/tmp/path001',
                "Path '/tmp/../etc/passwd' is out of working directory '/tmp'",
                '/tmp'
            ),
            'copy second path invalid' => array(
                'copy',
                '/tmp/uploaded.txt',
                '/tmp/../etc/passwd',
                "Path '/tmp/../etc/passwd' is out of working directory '/tmp'",
            ),
            'copy both path invalid' => array(
                'copy',
                '/tmp/../etc/passwd',
                '/tmp/../dev/null',
                "Path '/tmp/../etc/passwd' is out of working directory '/tmp'",
            ),
            'rename first path invalid' => array(
                'rename',
                '/tmp/../etc/passwd',
                '/tmp/path001',
                "Path '/tmp/../etc/passwd' is out of working directory '/tmp'",
            ),
            'rename first path invalid #2' => array(
                'rename',
                '/tmp/../etc/passwd',
                '/tmp/path001',
                "Path '/tmp/../etc/passwd' is out of working directory '/tmp'",
                '/tmp'
            ),
            'rename second path invalid' => array(
                'rename',
                '/tmp/uploaded.txt',
                '/tmp/../etc/passwd',
                "Path '/tmp/../etc/passwd' is out of working directory '/tmp'",
            ),
            'rename both path invalid' => array(
                'rename',
                '/tmp/../etc/passwd',
                '/tmp/../dev/null',
                "Path '/tmp/../etc/passwd' is out of working directory '/tmp'",
            ),
            'copy target path invalid' => array(
                'copy',
                '/tmp/passwd',
                '/etc/../dev/null',
                "Path '/etc/../dev/null' is out of working directory '/etc'",
                null,
                '/etc'
            ),
            'rename target path invalid' => array(
                'rename',
                '/tmp/passwd',
                '/etc/../dev/null',
                "Path '/etc/../dev/null' is out of working directory '/etc'",
                null,
                '/etc'
            ),
            'copy target path invalid #2' => array(
                'copy',
                '/tmp/passwd',
                '/etc/../dev/null',
                "Path '/etc/../dev/null' is out of working directory '/etc'",
                '/tmp',
                '/etc'
            ),
            'rename target path invalid #2' => array(
                'rename',
                '/tmp/passwd',
                '/etc/../dev/null',
                "Path '/etc/../dev/null' is out of working directory '/etc'",
                '/tmp',
                '/etc'
            ),
        );
    }

    public function testEnsureDirectoryExists()
    {
        $dir = '/tmp/path';
        $adapterMock = $this->getMockBuilder('Magento\Filesystem\AdapterInterface')
            ->getMock();
        $adapterMock->expects($this->at(0))
            ->method('isDirectory')
            ->with('/tmp')
            ->will($this->returnValue(true));
        $adapterMock->expects($this->at(1))
            ->method('isDirectory')
            ->with($dir)
            ->will($this->returnValue(true));
        $adapterMock->expects($this->exactly(2))
            ->method('isDirectory');
        $adapterMock->expects($this->never())
            ->method('createDirectory');
        $filesystem = new \Magento\Filesystem($adapterMock);
        $filesystem->setWorkingDirectory('/tmp');
        $filesystem->ensureDirectoryExists($dir, 0644);
    }

    /**
     * @expectedException \Magento\Filesystem\FilesystemException
     * @expectedExceptionMessage Directory '/tmp/path' doesn't exist.
     */
    public function testEnsureDirectoryExistsException()
    {
        $dir = '/tmp/path';
        $adapterMock = $this->getMockBuilder('Magento\Filesystem\AdapterInterface')
            ->getMock();
        $adapterMock->expects($this->at(0))
            ->method('isDirectory')
            ->with('/tmp')
            ->will($this->returnValue(true));
        $adapterMock->expects($this->at(1))
            ->method('isDirectory')
            ->with($dir)
            ->will($this->returnValue(false));
        $adapterMock->expects($this->exactly(2))
            ->method('isDirectory');
        $adapterMock->expects($this->never())
            ->method('createDirectory');
        $filesystem = new \Magento\Filesystem($adapterMock);
        $filesystem->setWorkingDirectory('/tmp');
        $filesystem->ensureDirectoryExists($dir, 0644);
    }

    public function testEnsureDirectoryExistsNoDir()
    {
        $dir = '/tmp/path1/path2';
        $adapterMock = $this->getMockBuilder('Magento\Filesystem\AdapterInterface')
            ->getMock();
        $adapterMock->expects($this->at(0))
            ->method('isDirectory')
            ->with('/tmp')
            ->will($this->returnValue(true));
        $adapterMock->expects($this->at(1))
            ->method('isDirectory')
            ->with($dir)
            ->will($this->returnValue(false));
        $adapterMock->expects($this->at(2))
            ->method('isDirectory')
            ->with('/tmp/path1')
            ->will($this->returnValue(false));
        $adapterMock->expects($this->at(3))
            ->method('isDirectory')
            ->with('/tmp')
            ->will($this->returnValue(true));
        $adapterMock->expects($this->exactly(4))
            ->method('isDirectory');
        $adapterMock->expects($this->at(4))
            ->method('createDirectory')
            ->with('/tmp/path1');
        $adapterMock->expects($this->at(5))
            ->method('createDirectory')
            ->with('/tmp/path1/path2');
        $adapterMock->expects($this->exactly(2))
            ->method('createDirectory');
        $filesystem = new \Magento\Filesystem($adapterMock);
        $filesystem->setWorkingDirectory('/tmp');
        $filesystem->setIsAllowCreateDirectories(true);
        $filesystem->ensureDirectoryExists($dir, 0644);
    }

    /**
     * @dataProvider allowCreateDirsDataProvider
     * @param bool $allowCreateDirs
     */
    public function testTouch($allowCreateDirs)
    {
        $validPath = '/tmp/path/file.txt';
        $adapterMock = $this->getMockBuilder('Magento\Filesystem\AdapterInterface')
            ->getMock();
        $adapterMock->expects($this->exactly(2))
            ->method('isDirectory')
            ->will($this->returnValue(true));

        $filesystem = new \Magento\Filesystem($adapterMock);
        $filesystem->setIsAllowCreateDirectories($allowCreateDirs);
        $filesystem->setWorkingDirectory('/tmp');
        $filesystem->touch($validPath);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Path '/etc/passwd' is out of working directory '/tmp'
     */
    public function testTouchIsolation()
    {
        $filesystem = new \Magento\Filesystem($this->_getDefaultAdapterMock());
        $filesystem->setWorkingDirectory('/tmp');
        $filesystem->touch('/etc/passwd');
    }

    /**
     * @return array
     */
    public function allowCreateDirsDataProvider()
    {
        return array(array(true), array(false));
    }

    public function testCreateStreamCustom()
    {
        $path = '/tmp/test.txt';
        $streamMock = $this->getMockBuilder('Magento\Filesystem\Stream\Local')
            ->disableOriginalConstructor()
            ->getMock();
        $adapterMock = $this->getMockBuilder('Magento\Filesystem\Adapter\Local')
            ->getMock();
        $adapterMock->expects($this->once())
            ->method('isDirectory')
            ->with('/tmp')
            ->will($this->returnValue(true));
        $adapterMock->expects($this->once())
            ->method('createStream')
            ->with($path)
            ->will($this->returnValue($streamMock));
        $filesystem = new \Magento\Filesystem($adapterMock);
        $filesystem->setWorkingDirectory('/tmp');
        $this->assertInstanceOf('Magento\Filesystem\Stream\Local', $filesystem->createStream($path));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Path '/tmp/../etc/test.txt' is out of working directory '/tmp'
     */
    public function testCreateStreamIsolation()
    {
        $adapterMock = $this->getMockBuilder('Magento\Filesystem\Adapter\Local')
            ->getMock();
        $adapterMock->expects($this->once())
            ->method('isDirectory')
            ->with('/tmp')
            ->will($this->returnValue(true));
        $filesystem = new \Magento\Filesystem($adapterMock);
        $filesystem->setWorkingDirectory('/tmp');
        $filesystem->createStream('/tmp/../etc/test.txt');
    }

    /**
     * @expectedException \Magento\Filesystem\FilesystemException
     * @expectedExceptionMessage Filesystem doesn't support streams.
     */
    public function testCreateStreamException()
    {
        $filesystem = new \Magento\Filesystem($this->_getDefaultAdapterMock());
        $filesystem->setWorkingDirectory('/tmp');
        $filesystem->createStream('/tmp/test.txt');
    }

    /**
     * @dataProvider modeDataProvider
     * @param string|\Magento\Filesystem\Stream\Mode $mode
     */
    public function testCreateAndOpenStream($mode)
    {
        $path = '/tmp/test.txt';
        $streamMock = $this->getMockBuilder('Magento\Filesystem\Stream\Local')
            ->disableOriginalConstructor()
            ->getMock();
        $streamMock->expects($this->once())
            ->method('open');
        $adapterMock = $this->getMockBuilder('Magento\Filesystem\Adapter\Local')
            ->getMock();
        $adapterMock->expects($this->once())
            ->method('isDirectory')
            ->with('/tmp')
            ->will($this->returnValue(true));
        $adapterMock->expects($this->once())
            ->method('createStream')
            ->with($path)
            ->will($this->returnValue($streamMock));
        $filesystem = new \Magento\Filesystem($adapterMock);
        $filesystem->setWorkingDirectory('/tmp');
        $this->assertInstanceOf('Magento\Filesystem\Stream\Local', $filesystem->createAndOpenStream($path, $mode));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Wrong mode parameter
     */
    public function testCreateAndOpenStreamException()
    {
        $path = '/tmp/test.txt';
        $streamMock = $this->getMockBuilder('Magento\Filesystem\Stream\Local')
            ->disableOriginalConstructor()
            ->getMock();
        $streamMock->expects($this->never())
            ->method('open');
        $adapterMock = $this->getMockBuilder('Magento\Filesystem\Adapter\Local')
            ->getMock();
        $adapterMock->expects($this->once())
            ->method('isDirectory')
            ->with('/tmp')
            ->will($this->returnValue(true));
        $adapterMock->expects($this->once())
            ->method('createStream')
            ->with($path)
            ->will($this->returnValue($streamMock));
        $filesystem = new \Magento\Filesystem($adapterMock);
        $filesystem->setWorkingDirectory('/tmp');
        $this->assertInstanceOf('Magento\Filesystem\Stream\Local',
            $filesystem->createAndOpenStream($path, new \stdClass()));
    }

    /**
     * @return array
     */
    public function modeDataProvider()
    {
        return array(
            array('r'),
            array(new \Magento\Filesystem\Stream\Mode('w'))
        );
    }

    /**
     * @dataProvider adapterMethods
     * @param string $method
     * @param string $adapterMethod
     * @param array|null $params
     */
    public function testAdapterMethods($method, $adapterMethod, array $params = null)
    {
        $validPath = '/tmp/path/file.txt';
        $adapterMock = $this->_getDefaultAdapterMock();
        $adapterMock->expects($this->once())
            ->method($adapterMethod)
            ->with($validPath);

        $filesystem = new \Magento\Filesystem($adapterMock);
        $filesystem->setWorkingDirectory('/tmp');
        $params = (array)$params;
        array_unshift($params, $validPath);
        call_user_func_array(array($filesystem, $method), $params);
    }

    /**
     * @return array
     */
    public function adapterMethods()
    {
        return array(
            'exists' => array('has', 'exists'),
            'delete' => array('delete', 'delete'),
            'isFile' => array('isFile', 'isFile'),
            'isWritable' => array('isWritable', 'isWritable'),
            'isReadable' => array('isReadable', 'isReadable'),
            'getNestedKeys' => array('getNestedKeys', 'getNestedKeys'),
            'changePermissions' => array('changePermissions', 'changePermissions', array(0777, true)),
            'exists #2' => array('has', 'exists', array('/tmp')),
            'delete #2' => array('delete', 'delete', array('/tmp')),
            'isFile #2' => array('isFile', 'isFile', array('/tmp')),
            'isWritable #2' => array('isWritable', 'isWritable', array('/tmp')),
            'isReadable #2' => array('isReadable', 'isReadable', array('/tmp')),
            'getNestedKeys #2' => array('getNestedKeys', 'getNestedKeys', array('/tmp')),
            'changePermissions #2' => array('changePermissions', 'changePermissions', array(0777, true, '/tmp')),
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Path '/tmp/../etc/passwd' is out of working directory '/tmp'
     * @dataProvider adapterIsolationMethods
     * @param string $method
     * @param string $adapterMethod
     * @param array|null $params
     */
    public function testIsolationException($method, $adapterMethod, array $params = null)
    {
        $invalidPath = '/tmp/../etc/passwd';
        $adapterMock = $this->_getDefaultAdapterMock();
        $adapterMock->expects($this->never())
            ->method($adapterMethod);

        $filesystem = new \Magento\Filesystem($adapterMock);
        $filesystem->setWorkingDirectory('/tmp');
        $params = (array)$params;
        array_unshift($params, $invalidPath);
        call_user_func_array(array($filesystem, $method), $params);
    }

    /**
     * @return array
     */
    public function adapterIsolationMethods()
    {
        return $this->adapterMethods()
            + array(
                'mtime' => array('getMTime', 'getMTime'),
                'read' => array('read', 'read'),
                'read #2' => array('read', 'read', array('/tmp')),
                'createDirectory' => array('createDirectory', 'createDirectory', array(0777)),
                'createDirectory #2' => array('createDirectory', 'createDirectory', array(0777, '/tmp')),
                'getFileMd5' => array('getFileMd5', 'getFileMd5'),
                'getFileSize' => array('getFileSize', 'getFileSize')
            );
    }

    /**
     * @dataProvider adapterMethodsWithFileCheckDataProvider
     * @param string $method
     * @param string $adapterMethod
     */
    public function testAdapterMethodsWithFileChecks($method, $adapterMethod)
    {
        $validPath = '/tmp/path/file.txt';
        $adapterMock = $this->_getDefaultAdapterMock();
        $adapterMock->expects($this->once())
            ->method('isFile')
            ->with($validPath)
            ->will($this->returnValue(true));
        $adapterMock->expects($this->once())
            ->method($adapterMethod)
            ->with($validPath)
            ->will($this->returnValue(1));

        $filesystem = new \Magento\Filesystem($adapterMock);
        $filesystem->setWorkingDirectory('/tmp');
        $this->assertEquals(1, $filesystem->$method($validPath));
    }

    /**
     * @return array
     */
    public function adapterMethodsWithFileCheckDataProvider()
    {
        return array(
            'read' => array('read', 'read'),
            'getFileMd5' => array('getFileMd5', 'getFileMd5'),
            'getFileSize' => array('getFileSize', 'getFileSize')
        );
    }

    /**
     * @dataProvider workingDirDataProvider
     * @param string|null $workingDirectory
     */
    public function testCreateDirectory($workingDirectory)
    {
        $validPath = '/tmp/path';
        $adapterMock = $this->getMockBuilder('Magento\Filesystem\AdapterInterface')
            ->getMock();
        $adapterMock->expects($this->exactly(2))
            ->method('isDirectory')
            ->with('/tmp')
            ->will($this->returnValue(true));
        $adapterMock->expects($this->once())
            ->method('createDirectory')
            ->with($validPath);

        $filesystem = new \Magento\Filesystem($adapterMock);
        $filesystem->setWorkingDirectory('/tmp');
        $filesystem->createDirectory($validPath, 0777, $workingDirectory);
    }

    /**
     * @dataProvider workingDirDataProvider
     * @param string|null $workingDirectory
     */
    public function testWrite($workingDirectory)
    {
        $validPath = '/tmp/path/file.txt';
        $adapterMock = $this->getMockBuilder('Magento\Filesystem\AdapterInterface')
            ->getMock();
        $adapterMock->expects($this->at(0))
            ->method('isDirectory')
            ->with('/tmp')
            ->will($this->returnValue(true));
        $adapterMock->expects($this->at(1))
            ->method('isDirectory')
            ->with('/tmp/path')
            ->will($this->returnValue(true));
        $adapterMock->expects($this->exactly(2))
            ->method('isDirectory');
        $adapterMock->expects($this->once())
            ->method('write')
            ->with($validPath, 'TEST TEST');

        $filesystem = new \Magento\Filesystem($adapterMock);
        $filesystem->setWorkingDirectory('/tmp');
        $filesystem->write($validPath, 'TEST TEST', $workingDirectory);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Path '/tmp/../path/file.txt' is out of working directory '/tmp'
     * @dataProvider workingDirDataProvider
     * @param string|null $workingDirectory
     */
    public function testWriteIsolation($workingDirectory)
    {
        $invalidPath = '/tmp/../path/file.txt';
        $adapterMock = $this->getMockBuilder('Magento\Filesystem\AdapterInterface')
            ->getMock();
        $adapterMock->expects($this->once())
            ->method('isDirectory')
            ->with('/tmp')
            ->will($this->returnValue(true));
        $adapterMock->expects($this->never())
            ->method('write');

        $filesystem = new \Magento\Filesystem($adapterMock);
        $filesystem->setWorkingDirectory('/tmp');
        $filesystem->write($invalidPath, 'TEST TEST', $workingDirectory);
    }

    /**
     * @return array
     */
    public function workingDirDataProvider()
    {
        return array(
            array(null), array('/tmp')
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "/tmp/test/file.txt" does not exists
     * @dataProvider methodsWithFileChecksDataProvider
     * @param string $method
     * @param array|null $params
     */
    public function testFileChecks($method, array $params = null)
    {
        $path = '/tmp/test/file.txt';
        $adapterMock = $this->getMockBuilder('Magento\Filesystem\AdapterInterface')
            ->getMock();
        $adapterMock->expects($this->once())
            ->method('isDirectory')
            ->with('/tmp')
            ->will($this->returnValue(true));
        $adapterMock->expects($this->once())
            ->method('exists')
            ->with($path)
            ->will($this->returnValue(false));
        $filesystem = new \Magento\Filesystem($adapterMock);
        $filesystem->setWorkingDirectory('/tmp');
        $params = (array)$params;
        array_unshift($params, $path);
        call_user_func_array(array($filesystem, $method), $params);
    }

    /**
     * @return array
     */
    public function methodsWithFileChecksDataProvider()
    {
        return array(
            'rename' => array('rename', array('/tmp/file001.txt'))
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "/tmp/test/file.txt" does not exists
     * @dataProvider methodsWithPathChecksDataProvider
     * @param string $method
     * @param array|null $params
     */
    public function testPathChecks($method, array $params = null)
    {
        $path = '/tmp/test/file.txt';
        $adapterMock = $this->getMockBuilder('Magento\Filesystem\AdapterInterface')
            ->getMock();
        $adapterMock->expects($this->once())
            ->method('isDirectory')
            ->with('/tmp')
            ->will($this->returnValue(true));
        $adapterMock->expects($this->once())
            ->method('isFile')
            ->with($path)
            ->will($this->returnValue(false));
        $filesystem = new \Magento\Filesystem($adapterMock);
        $filesystem->setWorkingDirectory('/tmp');
        $params = (array)$params;
        array_unshift($params, $path);
        call_user_func_array(array($filesystem, $method), $params);
    }

    /**
     * @return array
     */
    public function methodsWithPathChecksDataProvider()
    {
        return array(
            'read' => array('read'),
            'copy' => array('copy', array('/tmp/file001.txt')),
        );
    }

    /**
     * Test isDirectory
     *
     * @dataProvider workingDirDataProvider
     * @param string|null $workingDirectory
     */
    public function testIsDirectory($workingDirectory)
    {
        $validPath = '/tmp/path';
        $adapterMock = $this->getMockBuilder('Magento\Filesystem\AdapterInterface')
            ->getMock();
        $adapterMock->expects($this->at(0))
            ->method('isDirectory')
            ->with('/tmp')
            ->will($this->returnValue(true));
        $adapterMock->expects($this->at(1))
            ->method('isDirectory')
            ->with($validPath)
            ->will($this->returnValue(true));
        $adapterMock->expects($this->exactly(2))
            ->method('isDirectory');

        $filesystem = new \Magento\Filesystem($adapterMock);
        $filesystem->setWorkingDirectory('/tmp');
        $this->assertTrue($filesystem->isDirectory($validPath, $workingDirectory));
    }

    /**
     * Test isDirectory isolation
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Path '/tmp/../etc/passwd' is out of working directory '/tmp'
     * @dataProvider workingDirDataProvider
     * @param string|null $workingDirectory
     */
    public function testIsDirectoryIsolation($workingDirectory)
    {
        $validPath = '/tmp/../etc/passwd';
        $filesystem = new \Magento\Filesystem($this->_getDefaultAdapterMock());
        $filesystem->setWorkingDirectory('/tmp');
        $this->assertTrue($filesystem->isDirectory($validPath, $workingDirectory));
    }

    /**
     * @dataProvider normalizePathDataProvider
     * @param string $path
     * @param bool $isRelative
     * @param string $expected
     */
    public function testNormalizePath($path, $isRelative, $expected)
    {
        $this->assertEquals($expected, \Magento\Filesystem::normalizePath($path, $isRelative));
    }

    /**
     * @return array
     */
    public static function normalizePathDataProvider()
    {
        return array(
            array('/tmp/../file.txt', false, '/file.txt'),
            array('/tmp/../etc/mysql/file.txt', false, '/etc/mysql/file.txt'),
            array('/tmp/./file.txt', false, '/tmp/file.txt'),
            array('/tmp/./../file.txt', false, '/file.txt'),
            array('/tmp/path/file.txt', false, '/tmp/path/file.txt'),
            array('/tmp/path\file.txt', false, '/tmp/path/file.txt'),
            array('/tmp/path', false, '/tmp/path'),
            array('../tmp/path', true, '../tmp/path'),
            array('../tmp/../../path', true, '../../path'),
            array('C:\\Windows', false, 'C:/Windows'),
            array('C:\\Windows\\system32\\..', false, 'C:/Windows'),
        );
    }


    /**
     * @param string $path
     * @param bool $isRelative
     * @dataProvider normalizePathExceptionDataProvider
     */
    public function testNormalizePathException($path, $isRelative)
    {
        $this->setExpectedException('Magento\Filesystem\FilesystemException', "Invalid path '{$path}'.");
        \Magento\Filesystem::normalizePath($path, $isRelative);
    }

    /**
     * @return array
     */
    public static function normalizePathExceptionDataProvider()
    {
        return array(
            array('./../file.txt', false),
            array('/../file.txt', false),
            array('/tmp/../../file.txt', false),
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getDefaultAdapterMock()
    {
        $adapterMock = $this->getMockBuilder('Magento\Filesystem\AdapterInterface')
            ->getMock();
        $adapterMock->expects($this->once())
            ->method('isDirectory')
            ->with('/tmp')
            ->will($this->returnValue(true));
        $adapterMock->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true));
        return $adapterMock;
    }

    /**
     * @dataProvider isPathInDirectoryDataProvider
     * @param string $path
     * @param string $directory
     * @param boolean $expectedValue
     */
    public function testIsPathInDirectory($path, $directory, $expectedValue)
    {
        $adapterMock = $this->getMockBuilder('Magento\Filesystem\AdapterInterface')
            ->getMock();

        $filesystem = new \Magento\Filesystem($adapterMock);
        $this->assertEquals($expectedValue, $filesystem->isPathInDirectory($path, $directory));
    }

    /**
     * @return array
     */
    public function isPathInDirectoryDataProvider()
    {
        return array(
            array('/tmp/file', '/tmp', true),
            array('/tmp/file', '/tmp/dir', false),
            array('/tmp', '/tmp/', true),
            array('/tmp/', '/tmp', true),
        );
    }


    /**
     * @dataProvider testSearchFilesDataProvider
     * @param string $workingDirectory
     * @param string $baseDirectory
     * @param string $pattern
     * @param string $expectedValue
     */
    public function testSearchFiles($workingDirectory, $baseDirectory, $pattern, $expectedValue)
    {
        $adapterMock = $this->getMock('Magento\Filesystem\AdapterInterface');

        $adapterMock->expects($this->once())
            ->method('isDirectory')
            ->with($workingDirectory)
            ->will($this->returnValue(true));

        $searchResult = array('result');
        $adapterMock->expects($this->once())
            ->method('searchKeys')
            ->with($expectedValue)
            ->will($this->returnValue($searchResult));

        $filesystem = new \Magento\Filesystem($adapterMock);
        $filesystem->setWorkingDirectory($workingDirectory);
        $this->assertEquals($searchResult, $filesystem->searchKeys($baseDirectory, $pattern));
    }

    public function testSearchFilesDataProvider()
    {
        return array(
            array('/tmp', '/tmp/some/folder', '*', '/tmp/some/folder/*'),
            array('/tmp', '/tmp/some/folder/', '/*', '/tmp/some/folder/*'),
            array('/tmp', '/tmp/some/folder/', '/../../*', '/tmp/some/folder/../../*'),
        );
    }

    /**
     * @dataProvider searchFilesIsolationDataProvider
     * @param string $workingDirectory
     * @param string $baseDirectory
     * @param string $pattern
     * @param string $expectedMessage
     */
    public function testSearchFilesIsolation($workingDirectory, $baseDirectory, $pattern, $expectedMessage)
    {
        $adapterMock = $this->getMock('Magento\Filesystem\AdapterInterface');

        $adapterMock->expects($this->once())
            ->method('isDirectory')
            ->with($workingDirectory)
            ->will($this->returnValue(true));

        $filesystem = new \Magento\Filesystem($adapterMock);
        $filesystem->setWorkingDirectory($workingDirectory);

        $this->setExpectedException('InvalidArgumentException', $expectedMessage);
        $filesystem->searchKeys($baseDirectory, $pattern);
    }

    public function searchFilesIsolationDataProvider()
    {
        return array(
            array(
                '/tmp',
                '/tmp/some/folder',
                '/../../../*',
                "Path '/tmp/some/folder/../../../*' is out of working directory '/tmp'"
            ),
            array(
                '/tmp/log',
                '/tmp/log/some/folder/../../../',
                '*',
                "Path '/tmp/log/some/folder/../../../' is out of working directory '/tmp/log'"
            ),
        );
    }
}
