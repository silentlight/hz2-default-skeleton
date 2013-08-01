<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Context\Step\Then,
	Behat\Behat\Context\Step\When,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;

//
// Require 3rd-party libraries here:
//
require_once 'C:/wamp/www/Tutorials/Web_Developer/PHP/Zend2/vendor/autoload.php';

/**
 * Features context.
 */
class UIContext extends MinkContext
{
	protected $page_list = array(
		'home' => '/'
	);
	
	protected $element_list = array(
		'albums table' => '.albums',
		'user table' => '.conferences'
	);
	
    public function __construct(array $parameters)
    {
      
    }
	
	 /** 
	  * @When /^I am on the "([^"]*)" page$/ 
	  */ 
	 public function iAmOnThePage($page_name) 
	 {
	 	 if(!isset($this->page_list[$page_name])){
	 	 	throw new Exception( "{$page_name}: not in page list"); 
		 } 
		 
		 $page = $this->page_list[$page_name]; 
		 
		 return new When("I am on \"{$page}\""); 
	 }
	 
	 /** 
	  * @Then /^I should see "([^"]*)" in the "([^"]*)"$/ 
	  */ 
	 public function iShouldSeeInThe($text, $element) 
	 {
	 	 if(!isset($this->element_list[$element])) {
	 	 	 throw new Exception( "Element: {$element} not in element list"); 
		 } 
		 
		 $element = $this->element_list[$element]; 
		 
		 return new Then("I should see \"{$text}\" in the \"{$element}\" element"); 
	 }
	 
	 /**
	  * @When /^I wait (\d+) seconds?$/
	  */
	 public function waitSeconds($seconds)
	 {
	 	$this->getSession()->wait(1000 * $seconds);
	 }
}
