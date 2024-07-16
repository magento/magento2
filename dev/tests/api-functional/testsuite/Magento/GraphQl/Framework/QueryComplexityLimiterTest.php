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
        nameAlias: name
        ...configurableFields
        ... on BundleProduct {
          items {
            options {
              uid
              label
            }
          }
        }
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
              special_to_date
              tier_price
              manufacturer
              thumbnail {
                url
                label
              }
              sku
              image {
                url
                label
              }
              canonical_url
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
                    special_to_date
                    thumbnail {
                      url
                      label
                    }
                    tier_price
                    manufacturer
                    sku
                    image {
                      url
                      label
                    }
                    canonical_url
                    media_gallery_entries {
                      position
                      id
                      types
                    }
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
                          special_to_date
                          tier_price
                          manufacturer
                          thumbnail {
                            url
                            label
                          }
                          sku
                          image {
                            url
                            label
                          }
                          canonical_url
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
                                special_to_date
                                tier_price
                                manufacturer
                                sku
                                image {
                                  url
                                  label
                                }
                                canonical_url
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
                                      special_to_date
                                      price {
                                        minimalPrice {
                                          amount {
                                            value
                                            currency
                                          }
                                        }
                                        maximalPrice {
                                          amount {
                                            value
                                            currency
                                          }
                                        }
                                        regularPrice {
                                          amount {
                                            value
                                            currency
                                          }
                                        }
                                      }
                                      tier_price
                                      special_price
                                      tier_prices {
                                        customer_group_id
                                        qty
                                        percentage_value
                                        website_id
                                      }
                                      tier_prices {
                                        customer_group_id
                                        qty
                                        percentage_value
                                        website_id
                                      }
                                      tier_prices {
                                        customer_group_id
                                        qty
                                        percentage_value
                                        website_id
                                      }
                                      tier_prices {
                                        customer_group_id
                                        qty
                                        percentage_value
                                        website_id
                                      }
                                      tier_prices {
                                        customer_group_id
                                        qty
                                        percentage_value
                                        website_id
                                      }
                                      tier_prices {
                                        customer_group_id
                                        qty
                                        percentage_value
                                        website_id
                                      }
                                      tier_prices {
                                        customer_group_id
                                        qty
                                        percentage_value
                                        website_id
                                      }
                                      tier_prices {
                                        customer_group_id
                                        qty
                                        percentage_value
                                        website_id
                                      }
                                      tier_prices {
                                        customer_group_id
                                        qty
                                        percentage_value
                                        website_id
                                      }
                                      tier_prices {
                                        customer_group_id
                                        qty
                                        percentage_value
                                        website_id
                                      }
                                      tier_prices {
                                        customer_group_id
                                        qty
                                        percentage_value
                                        website_id
                                      }
                                      tier_prices {
                                        customer_group_id
                                        qty
                                        percentage_value
                                        website_id
                                      }
                                      tier_prices {
                                        customer_group_id
                                        qty
                                        percentage_value
                                        website_id
                                      }
                                      tier_prices {
                                        customer_group_id
                                        qty
                                        percentage_value
                                        website_id
                                      }
                                      tier_prices {
                                        customer_group_id
                                        qty
                                        percentage_value
                                        website_id
                                      }
                                      tier_prices {
                                        customer_group_id
                                        qty
                                        percentage_value
                                        website_id
                                      }
                                      tier_prices {
                                        customer_group_id
                                        qty
                                        percentage_value
                                        website_id
                                      }
                                      tier_prices {
                                        customer_group_id
                                        qty
                                        percentage_value
                                        website_id
                                      }
                                      tier_prices {
                                        customer_group_id
                                        qty
                                        percentage_value
                                        website_id
                                      }
                                      tier_prices {
                                        customer_group_id
                                        qty
                                        percentage_value
                                        website_id
                                      }
                                      tier_prices {
                                        customer_group_id
                                        qty
                                        percentage_value
                                        website_id
                                      }
                                      tier_price
                                      manufacturer
                                      sku
                                      image {
                                        url
                                        label
                                      }
                                      thumbnail {
                                        url
                                        label
                                      }
                                      canonical_url
                                      categories {
                                        id
                                        position
                                        position
                                        position
                                        position
                                        position
                                        position
                                        position
                                        position
                                        position
                                        position
                                        position
                                        position
                                        position
                                        position
                                        position
                                        position
                                        position
                                        position
                                        position
                                        position
                                        position
                                        position
                                        position
                                        position
                                        position
                                        position
                                        position
                                        position
                                        position
                                        level
                                        url_key
                                        url_path
                                        product_count
                                        default_sort_by
                                        breadcrumbs {
                                          category_id
                                          category_name
                                          category_url_key
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

fragment configurableFields on ConfigurableProduct {
  variants {
    attributes {
      uid
      code
      label
    }
  }
}
QUERY;

        self::expectExceptionMessageMatches('/Max query complexity should be 300 but got 302/');
        //Use POST request because request uri is too large for some servers
        $this->graphQlMutation($query);
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
    }
  }
}
QUERY;
        self::expectExceptionMessageMatches('/Max query depth should be 20 but got 23/');
        $this->graphQlQuery($query);
    }
}
