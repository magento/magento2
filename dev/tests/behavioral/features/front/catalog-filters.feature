@javascript
Feature: Catalog category filters

  Scenario: Filtering products

    Given I am on "/index.php/women.html"

    And I wait for element with xpath "//*[@id='maincontent']/div[4]/div[2]/div/div/ul[1]/li[2]/a" to appear
    And I click on the element with xpath "//*[@id='maincontent']/div[4]/div[2]/div/div/ul[1]/li[2]/a"

    And I wait for page to load "/index.php/women/tops-women/jackets-women.html"
    And I wait for element with xpath "//*[@id='narrow-by-list']/div[1]/div[1]" to appear
    And I click on the element with xpath "//*[@id='narrow-by-list']/div[1]/div[1]"
    And I wait for element with xpath "//*[@id='narrow-by-list']/div[1]/div[2]/ol/li[7]/a" to appear
    And I click on the element with xpath "//*[@id='narrow-by-list']/div[1]/div[2]/ol/li[7]/a"
    Then I wait for page to load "/index.php/women/tops-women/jackets-women.html?style_general=125"

    #Material
    And I wait for element with xpath "//*[@id='narrow-by-list']/div[4]/div[1]" to appear
    And I click on the element with xpath "//*[@id='narrow-by-list']/div[4]/div[1]"
    And I wait for element with xpath "//*[@id='narrow-by-list']/div[4]/div[2]/ol/li[3]/a" to appear
    And I click on the element with xpath "//*[@id='narrow-by-list']/div[4]/div[2]/ol/li[3]/a"

    #Price
    And I wait for element with xpath "//*[@id='narrow-by-list']/div[2]/div[1]" to appear
    And I click on the element with xpath "//*[@id='narrow-by-list']/div[2]/div[1]"
    And I wait for element with xpath "//*[@id='narrow-by-list']/div[2]/div[2]/ol/li[1]/a" to appear
    And I click on the element with xpath "//*[@id='narrow-by-list']/div[2]/div[2]/ol/li[1]/a"

    And I wait for element with xpath "//*[@id='maincontent']/div[3]/div[1]/div[2]/div[1]/strong[2]" to appear
    And I click on the element with xpath "//*[@id='maincontent']/div[3]/div[1]/div[2]/div[1]/strong[2]"

