<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Test\Integration\Model\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DB\Select;
use Magento\MediaGalleryUi\Model\SearchCriteria\CollectionProcessor\FilterProcessor\Directory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for Directory filter processor with case-sensitive path filtering
 *
 * @magentoDbIsolation enabled
 */
class DirectoryIntegrationTest extends TestCase
{
    /**
     * @var Directory
     */
    private $directoryFilterProcessor;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->directoryFilterProcessor = $objectManager->create(Directory::class);
    }

    /**
     * Test the FilterProcessor applies BINARY case-sensitive SQL query
     * This is the core test that verifies the BINARY keyword fix
     */
    public function testFilterProcessorAppliesBinaryCaseSensitiveQuery(): void
    {
        // Create mock collection and select objects
        $mockCollection = $this->createMock(AbstractDb::class);
        $mockSelect = $this->createMock(Select::class);
        
        $mockCollection->expects($this->once())
            ->method('getSelect')
            ->willReturn($mockSelect);
            
        // Verify that the BINARY keyword is used in the WHERE clause
        // This is the exact fix we implemented to make directory filtering case-sensitive
        $mockSelect->expects($this->once())
            ->method('where')
            ->with(
                $this->equalTo('BINARY path REGEXP ? '),
                $this->equalTo('^testing/[^\/]*$')
            );
        
        // Create filter for lowercase 'testing' directory
        $filter = Bootstrap::getObjectManager()->create(Filter::class);
        $filter->setField('directory');
        $filter->setValue('testing');
        
        // Apply the filter - this should call the BINARY SQL query
        $result = $this->directoryFilterProcessor->apply($filter, $mockCollection);
        $this->assertTrue($result);
    }

    /**
     * Test case-sensitive behavior with uppercase directory
     */
    public function testFilterProcessorWithUppercaseDirectory(): void
    {
        $mockCollection = $this->createMock(AbstractDb::class);
        $mockSelect = $this->createMock(Select::class);
        
        $mockCollection->expects($this->once())
            ->method('getSelect')
            ->willReturn($mockSelect);
            
        // Verify uppercase directory generates correct case-sensitive query
        $mockSelect->expects($this->once())
            ->method('where')
            ->with(
                $this->equalTo('BINARY path REGEXP ? '),
                $this->equalTo('^Testing/[^\/]*$')
            );
        
        $filter = Bootstrap::getObjectManager()->create(Filter::class);
        $filter->setField('directory');
        $filter->setValue('Testing');
        
        $result = $this->directoryFilterProcessor->apply($filter, $mockCollection);
        $this->assertTrue($result);
    }

    /**
     * Test that percentage signs are properly stripped from filter value
     */
    public function testFilterProcessorStripsPercentageSigns(): void
    {
        $mockCollection = $this->createMock(AbstractDb::class);
        $mockSelect = $this->createMock(Select::class);
        
        $mockCollection->expects($this->once())
            ->method('getSelect')
            ->willReturn($mockSelect);
            
        // Verify percentage signs are stripped from the regex pattern
        $mockSelect->expects($this->once())
            ->method('where')
            ->with(
                $this->equalTo('BINARY path REGEXP ? '),
                $this->equalTo('^TestingDirectory/[^\/]*$')
            );
        
        $filter = Bootstrap::getObjectManager()->create(Filter::class);
        $filter->setField('directory');
        $filter->setValue('Testing%Directory%');
        
        $result = $this->directoryFilterProcessor->apply($filter, $mockCollection);
        $this->assertTrue($result);
    }

    /**
     * Test with null filter value
     */
    public function testFilterProcessorWithNullValue(): void
    {
        $mockCollection = $this->createMock(AbstractDb::class);
        $mockSelect = $this->createMock(Select::class);
        
        $mockCollection->expects($this->once())
            ->method('getSelect')
            ->willReturn($mockSelect);
            
        // Verify null value creates empty directory pattern
        $mockSelect->expects($this->once())
            ->method('where')
            ->with(
                $this->equalTo('BINARY path REGEXP ? '),
                $this->equalTo('^/[^\/]*$')
            );
        
        $filter = Bootstrap::getObjectManager()->create(Filter::class);
        $filter->setField('directory');
        $filter->setValue(null);
        
        $result = $this->directoryFilterProcessor->apply($filter, $mockCollection);
        $this->assertTrue($result);
    }

    /**
     * Test regex pattern correctly excludes subdirectories
     * The pattern should match direct children only, not files in subdirectories
     */
    public function testRegexPatternExcludesSubdirectories(): void
    {
        $mockCollection = $this->createMock(AbstractDb::class);
        $mockSelect = $this->createMock(Select::class);
        
        $mockCollection->expects($this->once())
            ->method('getSelect')
            ->willReturn($mockSelect);
            
        // Verify the regex pattern uses [^\/]*$ to exclude subdirectories
        // This pattern matches: testing/file.jpg (✓)
        // But not: testing/subfolder/file.jpg (✗)
        $mockSelect->expects($this->once())
            ->method('where')
            ->with(
                $this->equalTo('BINARY path REGEXP ? '),
                $this->matchesRegularExpression('/\^\w+\/\[\\^\\\\\/\]\*\$/')
            );
        
        $filter = Bootstrap::getObjectManager()->create(Filter::class);
        $filter->setField('directory');
        $filter->setValue('testing');
        
        $result = $this->directoryFilterProcessor->apply($filter, $mockCollection);
        $this->assertTrue($result);
    }

    /**
     * Test with mixed case directory name
     */
    public function testFilterProcessorWithMixedCaseDirectory(): void
    {
        $mockCollection = $this->createMock(AbstractDb::class);
        $mockSelect = $this->createMock(Select::class);
        
        $mockCollection->expects($this->once())
            ->method('getSelect')
            ->willReturn($mockSelect);
            
        $mockSelect->expects($this->once())
            ->method('where')
            ->with(
                $this->equalTo('BINARY path REGEXP ? '),
                $this->equalTo('^MyTestDir/[^\/]*$')
            );
        
        $filter = Bootstrap::getObjectManager()->create(Filter::class);
        $filter->setField('directory');
        $filter->setValue('MyTestDir');
        
        $result = $this->directoryFilterProcessor->apply($filter, $mockCollection);
        $this->assertTrue($result);
    }
}
