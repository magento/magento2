# Magento_MediaGalleryApi module

The Magento_MediaGalleryApi module serves as application program interface (API) responsible for storing and managing media gallery asset attributes.

## Installation details

For information about module installation in Magento 2, see [Enable or disable modules](https://devdocs.magento.com/guides/v2.4/install-gde/install/cli/install-cli-subcommands-enable.html).

## Extensibility

Extension developers can interact with the Magento_MediaGalleryApi module. For more information about the Magento extension mechanism, see [Magento plug-ins](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/plugins.html).

[The Magento dependency injection mechanism](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/depend-inj.html) enables you to override the functionality of the Magento_MediaGalleryApi module.

### Public APIs

- `\Magento\MediaGalleryApi\Api\Data\AssetInterface`
    - media asset entity data

- `\Magento\MediaGalleryApi\Api\Data\AssetKeywordsInterface`
    - assets keywords aggregation

- `\Magento\MediaGalleryApi\Api\Data\AssetKeywordsInterface`
    - media asset keyword entity data

- `\Magento\MediaGalleryApi\Api\CreateDirectoriesByPathsInterface`:
    - create new directories by provided paths

- `\Magento\MediaGalleryApi\Api\DeleteAssetsByPathsInterface`:
    - delete media assets by paths. Removes all the assets which paths start with provided paths

- `\Magento\MediaGalleryApi\Api\DeleteDirectoriesByPathsInterface`:
    - delete folders by provided paths

- `\Magento\MediaGalleryApi\Api\GetAssetsByIdsInterface`:
    - get media gallery assets by id attribute
    
- `\Magento\MediaGalleryApi\Api\GetAssetsByPathsInterface`:
    - get media gallery assets by paths in media storage

- `\Magento\MediaGalleryApi\Api\GetAssetsKeywordsInterface`:
    - get a media gallery asset keywords related to media gallery asset ids provided

- `\Magento\MediaGalleryApi\Api\IsPathExcludedInterface`:
    - check if the path is excluded from displaying and processing in the media gallery

- `\Magento\MediaGalleryApi\Api\SaveAssetsInterface`:
    - save media gallery assets to the database

- `\Magento\MediaGalleryApi\Api\SaveAssetsKeywordsInterface`:
    - save keywords related to assets to the database
  
- `\Magento\MediaGalleryApi\Api\SearchAssetsInterface`:
    - search media gallery assets

For information about a public API in Magento 2, see [Public interfaces & APIs](http://devdocs.magento.com/guides/v2./extension-dev-guide/api-concepts.html).

## Additional information

For information about significant changes in patch releases, see [2.4.x Release information](https://devdocs.magento.com/guides/v2.4/release-notes/bk-release-notes.html).
