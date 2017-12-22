@javascript
Feature: Catalog category compare

  Scenario: Compare products

    Given I am on "/index.php/men.html"
    And I wait for element with xpath "//*[@id='maincontent']/div[4]/div[2]/div/div/ul[1]/li[2]/a" to appear
    And I click on the element with xpath "//*[@id='maincontent']/div[4]/div[2]/div/div/ul[1]/li[2]/a"

    #1 item to compare
    And I wait for element with xpath "//*[@id='maincontent']/div[3]/div[1]/div[3]/ol/li[1]/div/a/span/span/img" to appear
    And I click on the element with xpath "//*[@id='maincontent']/div[3]/div[1]/div[3]/ol/li[1]/div/a/span/span/img"
    Then I wait for page to load "/index.php/beaumont-summit-kit.html"
    And I wait for element with xpath "//*[@id='maincontent']/div[2]/div/div[1]/div[6]/div/a[2]/span" to appear
    And I click on the element with xpath "//*[@id='maincontent']/div[2]/div/div[1]/div[6]/div/a[2]/span"

    And I wait for element with xpath "//*[@id='maincontent']/div[1]/div[2]/div/div/div" to appear

    And I am on "/index.php/men.html"
    And I wait for element with xpath "//*[@id='maincontent']/div[4]/div[2]/div/div/ul[1]/li[2]/a" to appear
    And I click on the element with xpath "//*[@id='maincontent']/div[4]/div[2]/div/div/ul[1]/li[2]/a"

    #2 item to compare
    And I wait for element with xpath "//*[@id='maincontent']/div[3]/div[1]/div[3]/ol/li[4]/div/a/span/span/img" to appear
    And I click on the element with xpath "//*[@id='maincontent']/div[3]/div[1]/div[3]/ol/li[4]/div/a/span/span/img"
    Then I wait for page to load "/index.php/orion-two-tone-fitted-jacket.html"
    And I wait for element with xpath "//*[@id='maincontent']/div[2]/div/div[1]/div[6]/div/a[2]/span" to appear
    And I click on the element with xpath "//*[@id='maincontent']/div[2]/div/div[1]/div[6]/div/a[2]/span"

    And I wait for element with xpath "//*[@id='maincontent']/div[2]/div/div[1]/div[1]/h1/span" to appear
    And I am on "/index.php/men.html"

    #Compare
    And I wait for element with xpath "//*[@id='maincontent']/div[4]/div[3]/div[1]/div[2]/div/div[1]/a/span" to appear
    And I click on the element with xpath "//*[@id='maincontent']/div[4]/div[3]/div[1]/div[2]/div/div[1]/a/span"

    And I wait for page to load "/index.php/catalog/product_compare/index/"
    And I wait for element with xpath "//*[@id='product-comparison']/tbody[1]/tr/td[1]/div[3]/div[1]/form/button/span" to appear
    And I click on the element with xpath "//*[@id='product-comparison']/tbody[1]/tr/td[1]/div[3]/div[1]/form/button/span"
    And I wait for page to load "/index.php/beaumont-summit-kit.html"
