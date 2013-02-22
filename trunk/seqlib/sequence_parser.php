<?php

/**
* ------------------------------------------------------
*         Sequence Diagram Library: SeDiLib
* ------------------------------------------------------
*
* @copyright (C) 2013 Alexander Giesler (giesler.alexander@googlemail.com)
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License as
* published by the Free Software Foundation; either version 3 of
* the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public
* License along with this program; if not, see
* <http://www.gnu.org/licenses/>.
*/

require_once('sequence_builder.php');

/** Transforms sequence diagram represented in scripting language into an image
*/
class SequenceParser {    
    private $builder;
    
    private $errors = array();
    
    /** Searches $str in $mainStr, returns true if found
    * @param mainStr String in which to search
    * @param str String to search with
    * @param loc Line to start searching (explicit), default is false and not used
    */
    private function contains_substr($mainStr, $str, $loc = false) {
	// Convert both strings to lowercase
	$mainStr = strtolower($mainStr);
	$str = strtolower($str);
	
	if ($loc === false) return (strpos($mainStr, $str) !== false);
	if (strlen($mainStr) < strlen($str)) return false;
	if (($loc + strlen($str)) > strlen($mainStr)) return false;
	return (strcmp(substr($mainStr, $loc, strlen($str)), $str) == 0);
    }
    
    /** Constructor
    */
    public function __construct($text, &$errors = NULL) {
	$this->builder = new SequenceDiagramBuilder();
	
	$this->parseText($text);    
	$errors = $this->errors;
    }
    
    /** Generates image for sequence diagram
    * @param filename If empty, image will be send to browser, otherwise to file
    */
    public function generateImage($filename = '') {
	$this->builder->draw($filename);
    }
    
    /** Parse a text and create a sequence diagram
    * @param text The text, representing the diagram in the scripting languange
    */
    private function parseText($text) {
	$lines = explode("\r\n", $text);
	
	foreach($lines as $id => $line) {
	    $this->parseLine($id, $line);
	}
    }
    
    /** Parse current line and execute approproate command in builder
    * @param id Current line number
    * @param line Current line as string
    */ 
    private function parseLine($id, $line) {
	// Comment
	if ($this->contains_substr(trim($line), '#', 0)) {
	    // Do nothing
	}
	
	// Empty line
	else if (strlen(trim($line)) == 0) {
	    // Do nothing
	}
	
	// Add Class
	else if ($this->contains_substr($line, 'class:', 0)) {
	    $this->parseClass($id, $line);
	}
	
	// Title
	else if ($this->contains_substr($line, 'title:')) {
	    $this->parseTitle($id, $line);
	}
	
	// Note
	else if ($this->contains_substr($line, 'note:')) {
	    $this->parseNote($id, $line);
	}
	
	// Message
	else if ($this->contains_substr($line, '->') || 
		 $this->contains_substr($line, '<-')) 
	{
	    $this->parseMessage($id, $line);
	}
		
	// Activity
	else if ($this->contains_substr($line, '*')) {
	    $this->parseActivity($id, $line);
	}
	
	// Start or split block
	else if ($this->contains_substr($line, ':')) {
	    $this->parseBlock($id, $line);
	}
	
	// End block
	else if ($this->contains_substr($line, ';')) {
	    $this->builder->endBlock();
	}
	
	// Space
	else if ($this->contains_substr($line, '--')) {
	    $this->builder->addNote('', true);
	}
	
	// Error, nothing was detected
	else {
	    $this->errors[] = 'Syntax Error in line '.($id + 1).'.';
	}
    }
    
    /** Parse explicit class creation
    * Format:
    *   class: Name, Alias
    */
    private function parseClass($id, $line) {
	// Sample: class: Alice, A
	$class_name = '';
	$alias_name = '';
	
	// Extract class name
	$class_name = $line;
	$class_name = substr(strstr($class_name, ":"), 1);  // Get text right of 'class:'
	$class_name = strstr($class_name, ",", true);  // Remove alias if given
	$class_name = trim($class_name); // Remove whitespace
	
	// Extract alias name if possible
	$alias_name = $line;
	$alias_name = substr(strstr($alias_name, ":"), 1);  // Get text right of 'class:'
	$alias_name = substr(strstr($alias_name, ','), 1);  // Get text right of ','
	$alias_name = trim($alias_name); // Remove whitespace
		
	$this->builder->addClass($class_name, $alias_name);
    }
    
    /** Parse message
    * Format:
    *   Left -> Right : MessageText
    *   Left <- Right : MessageText
    *
    * Modifications:
    *    *    : Start / Stop Activity at destination / Origin (relative to side)
    *    +    : Create class at destination
    *    !    : Destroy class at destination
    *   --    : Use dashed line
    * << / >> : Use lined arrow head instead of filled
    */
    private function parseMessage($id, $line) {
	$direction = 'none';
	    
	// Get direction of message
	if ($this->contains_substr($line, '->')) $direction = 'right';
	if ($this->contains_substr($line, '<-')) $direction = 'left';
	
	// Get message
	$message_text = trim(substr(strstr($line, ':'), 1));
	
	// Tokens to filter out to get names
	$tokens = array('>', '<', '-', '*', '+', '!');
	
	// Get left name
	$left = trim(strstr($line, '-', true));
	
	// Get right name
	$right = substr(strstr($line, '-'), 1);
	$right = trim(strstr($right, ':', true));
	
	// Filter tokens
	$left = str_replace($tokens, '', $left);
	$right = str_replace($tokens, '', $right);
	
	// Get origin and destination by direction
	$origin = '';
	$destination = '';
	
	if ($direction == 'right') {
	    $origin = $left;
	    $destination = $right;
	}
	else if ($direction == 'left') {
	    $origin = $right;
	    $destination = $left;
	}
	else {
	    // Raise error message
	    $this->errors[] = 'Syntax Error in line '.($id + 1).', could not determine direction of message.';
	    return;
	}
	
	// Get message tokens by filter operations
	$message_tokens = $line;
	$message_tokens = strstr($line, ':', true); // Get text content right of colon
	$message_tokens = str_replace($left, '', $message_tokens); // Replace left name with blank
	$message_tokens = str_replace($right, '', $message_tokens); // Replace right name with blank
	$message_tokens = trim($message_tokens); // trim whitespace out
	
	// Informations to collect
	$dashed = false;
	$arrowHeadFilled = false;
	$startActivity = false;
	$stopActivity = false;
	$createClass = false;
	$destroyClass = false;
	
	// Message Arrow dashed ?
	if ($this->contains_substr($message_tokens, '--')) { $dashed = true; }
	
	// Filled Arrow Head ?
	if ($this->contains_substr($message_tokens, '>>')) { $arrowHeadFilled = true; }
	if ($this->contains_substr($message_tokens, '<<')) { $arrowHeadFilled = true; }
	
	// Split in left and right part of message tokens
	$split_message = array();
	
	if ($direction == 'right') {
	    $split_message = explode('->', $message_tokens);
	}
	else {
	    $split_message = explode('<-', $message_tokens);
	}
	
	// Check subsequent left and right part
	for ($i = 0; $i < 2; $i++) {
	    // Remove residues
	    $current_token_part = str_replace(array('-', '<', '>'), '', $split_message[$i]);
	    
	    // Activity start / stop
	    if ($this->contains_substr($current_token_part, '*')) {
		// If part and direction fit, activity start
		if ($i == 0 && $direction == 'left' || 
		    $i == 1 && $direction == 'right') {
		    $startActivity = true;
		}
		// Else activity stop
		else {
		    $stopActivity = true;
		}
	    }
	    
	    // Create class
	    if ($this->contains_substr($current_token_part, '+')) {
		// If part and direction fit, create class
		if ($i == 0 && $direction == 'left' || 
		    $i == 1 && $direction == 'right') {
		    $createClass = true;
		}
		// Else nonsense and error
		else {
		    // Raise error message
		    $this->errors[] = 'Syntax Error in line '.($id + 1).', tried creating a class with outgoing message.';
		    return;;
		}
	    }
	    
	    // Destroy class
	    if ($this->contains_substr($current_token_part, '!')) {
		// If part and direction fit, destroy class
		if ($i == 0 && $direction == 'left' || 
		    $i == 1 && $direction == 'right') {
		    $destroyClass = true;
		}
		// Else nonsense and error
		else {
		    // Raise error message
		    $this->errors[] = 'Syntax Error in line '.($id + 1).', tried destroying a class with outgoing message.';
		    return;;
		}
	    }
	}
	
	// Prepare message
	$activity_mode = 0;
	$class_mode = 0;
	
	// Activity mode
	if ($startActivity && !$stopActivity) { $activity_mode = 1; }
	if (!$startActivity && $stopActivity) { $activity_mode = 2; }
	if ($startActivity && $stopActivity)  { $activity_mode = 3; }
	
	// Class creation and destruction mode
	if ($createClass && !$destroyClass) { $class_mode = 1; }
	if (!$createClass && $destroyClass) { $class_mode = 2; }
	if ($createClass && $destroyClass) { 
	    // Raise error message
	    $this->errors[] = 'Syntax Error in line '.($id + 1).', tried creating and destroying class at same time.';
	    return;;
	}
	
	// Add message
	$this->builder->addMessage($origin, $destination, $message_text, $dashed, $arrowHeadFilled, $activity_mode, false, $class_mode);
    }

    /** Parse title
    * Format:
    *   title: text
    */
    private function parseTitle($id, $line) {
	$title = trim(substr($line, 7));
	$this->builder->setTitle($title);
    }
    
    /** Parse note
    * Format:
    *   note: text
    */
    private function parseNote($id, $line) {
	$note = trim(substr($line, 6));
	$this->builder->addNote($note);
    }
    
    /** Parse activity
    * Format:
    *  *Class  : Start Activity in class 'Class'
    *  Class*  : Stop Activity in class 'Class'
    */
    private function parseActivity($id, $line) {
	// Remove whitespace
	$line = trim($line);
	
	// Check if token is first or last character
	$splitted_text = explode('*', $line);
	
	// Check for error
	if (count($splitted_text) != 2) {
	    // Raise error message
	    $this->errors[] = 'Syntax Error in line '.($id + 1).', could not split command by activity token.';
	    return;
	}
	
	// Remove whitespace from left and right part
	$splitted_text[0] = trim($splitted_text[0]);
	$splitted_text[1] = trim($splitted_text[1]);
	
	// Extract class name and whether to start or stop an activity
	$class_name = '';
	$activity_mode = 0;
	
	// Check if activity token was left or right of class name
	if ($splitted_text[0] == '' && $splitted_text[1] != '') {
	    $class_name = $splitted_text[1];
	    $activity_mode = 2;
	}
	else if ($splitted_text[0] != '' && $splitted_text[1] == '') {
	    $class_name = $splitted_text[0];
	    $activity_mode = 1;
	}
	else {
	    // Raise error message
	    $this->errors[] = 'Syntax Error in line '.($id + 1).'.';
	    return;
	}
	
	// Add information to builder
	$this->builder->addMessage($class_name, $class_name, '', false, false, $activity_mode, true);
    }
    
    /** Parse block
    * Format:
    *   blockname : block text    : Start the Block with title 'blockname' and inner text 'block text'
    *   : block text              : Split current block and start new subblock with inner text 'block text'
    */
    private function parseBlock($id, $line) {
	$title = '';
	$text = '';
	
	// Extract title
	$title = $line;
	$title = strstr($title, ":", true);
	$title = trim($title); // Remove whitespace
	
	// Extract text
	$text = $line;
	$text = substr(strstr($text, ":"), 1);
	$text = trim($text); // Remove whitespace
	
	// If title empty, we habe a split command
	if ($title == '') {
	    $this->builder->splitBlock($text);
	}
	else {	    
	    // If title not empty, we have a create block command
	    
	    // But check if this is special REF block first
	    if ($title == 'REF') {
		$this->builder->addBlock('REF', $text, true);
		$this->builder->endBlock();
	    }
	    else {
		$this->builder->addBlock($title, $text);
	    }
	}
    }
}

?>