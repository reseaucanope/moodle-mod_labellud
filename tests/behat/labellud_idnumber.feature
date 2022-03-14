@mod @mod_labellud

Feature: set labellud idnumber
  In order to set labellud idnumber
  As a teacher
  I should create labellud activity and set an ID number

  @javascript
  Scenario: labellud ID number input box should be shown.
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Test | C1 | 0 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher | Teacher | Frist | teacher1@example.com |
      | student | Student | First | student1@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher | C1 | editingteacher |
      | student | C1 | student |
    Given I log in as "teacher"
    And I am on "Test" course homepage with editing mode on
    When I add a "labellud" to section "1" and I fill the form with:
      | Labellud text | Labellud with ID number set |
      | Availability | Show on course page |
      | ID number | C1LABEL1 |
    Then "Labellud with ID number set" activity should be visible
    And I turn editing mode off
    And "Labellud with ID number set" activity should be visible
    And I log out
    And I log in as "student"
    And I am on "Test" course homepage
    And I should see "Labellud with ID number set"
    And I log out
    And I log in as "teacher"
    And I am on "Test" course homepage
    And I turn editing mode on
    And I open "Labellud with ID number set" actions menu
    And I click on "Edit settings" "link" in the "Labellud with ID number set" activity
    And I expand all fieldsets
    And I should see "ID number" in the "Common module settings" "fieldset"
    And the field "ID number" matches value "C1LABELLUD1"
