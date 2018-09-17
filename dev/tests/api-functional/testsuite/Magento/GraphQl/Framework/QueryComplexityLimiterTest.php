<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Framework;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Tests query complexity limiter and depth limiter.
 * Actual for production mode only
 */
class QueryComplexityLimiterTest extends GraphQlAbstract
{
    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_virtual.php
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testQueryComplexityIsLimited()
    {
        $query
            = <<<QUERY
{
category(id: 2) {
    products {
      items {
        name
        categories {
          id
          position
          level
          url_key
          url_path
          product_count
          breadcrumbs {
            category_id
            category_name
            category_url_key
          }
          products {
            items {
              media_gallery_entries {
                file
              }
              name
              special_from_date
              special_to_date
              new_to_date
              new_from_date
              tier_price
              manufacturer
              thumbnail
              sku
              image
              canonical_url
              updated_at
              created_at
              categories {
                id
                position
                level
                url_key
                url_path
                product_count
                breadcrumbs {
                  category_id
                  category_name
                  category_url_key
                }
                products {
                  items {
                    name
                    special_from_date
                    special_to_date
                    new_to_date
                    thumbnail
                    new_from_date
                    tier_price
                    manufacturer
                    sku
                    image
                    canonical_url
                    updated_at
                    created_at
                    media_gallery_entries {
                      position
                      id
                      types
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
  }
}
QUERY;

        self::expectExceptionMessageRegExp('/Max query complexity should be 50 but got 62/');
        $this->graphQlQuery($query);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testQueryDepthIsLimited()
    {
        $query
            = <<<QUERY
{
  category(id: 2) {
    products {
      items {
        name
        categories {
          products {
            items {
              media_gallery_entries {
                file
              }
              categories {
                products {
                  items {
                    categories {
                      products {
                        items {
                          categories {
                            products {
                              items {
                                categories {
                                  products {
                                    items {
                                      categories {
                                        products {
                                          items {
                                            name
                                          }
                                        }
                                      }
                                    }
                                  }
                                }
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
  }
}
QUERY;
        self::expectExceptionMessageRegExp('/Max query depth should be 15 but got 20/');
        $this->graphQlQuery($query);
    }
}
