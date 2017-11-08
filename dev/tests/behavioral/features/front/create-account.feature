@javascript
Feature: Create account

  Scenario: Create account

    Given I am on "/"

    Then I wait for element with xpath "//div[1]/div/ul/li[3]/a" to appear
    And I click on the element with xpath "//div[1]/div/ul/li[3]/a"


    Then I wait for page to load "/index.php/customer/account/create/"

    #Personal information
    And I wait for element with xpath "//*[@id='firstname']" to appear
    And I fill in the following:
      | firstname          | Anne           |
      | lastname           | Liz            |
    And I check "is_subscribed"
    And I fill in the following:
      | email                  | newtest@gmail.com |
      | password               | Anne1234         |
      | password_confirmation  | Anne1234         |

    And I wait for element with xpath "//*[@id='form-validate']/div/div[1]/button/span" to appear
    And I click on the element with xpath "//*[@id='form-validate']/div/div[1]/button/span"

    #Success
    Then I wait for page to load "/index.php/customer/account/"
    And I wait for element containing unique text "Thank you for registering with Main Website Store." to appear