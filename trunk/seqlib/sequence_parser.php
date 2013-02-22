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

class SequenceParser {
    private $builder;
    
    private $errors = array();
    
    private function contains_substr($mainStr, $str, $loc = false) {
	if ($loc === false) return (strpos($mainStr, $str) !== false);
	if (strlen($mainStr) < strlen($str)) return false;
	if (($loc + strlen($str)) > strlen($mainStr)) return false;
	return (strcmp(substr($mainStr, $loc, strlen($str)), $str) == 0);
    }
    
    public function __construct($text, &$errors = NULL) {
	$this->builder = new SequenceDiagramBuilder();
	
	$this->parseText($text);    
	$errors = $this->errors;
    }
    
    public function generateImage($filename = '') {
	$this->builder->draw($filename);
    }
    
    private function parseText($text) {
	$lines = explode("\r\n", $text);
	
	foreach($lines as $id => $line) {
	    $this->parseLine($id, $line);
	}
    }
    
    private function parseLine($id, $line) {
	// Comment
	if ($this->contains_substr($line, '#', 0)) {
	}
	else if (strlen(trim($line)) == 0) {
	}
	// add Class
	else if ($this->contains_substr($line, 'class:', 0)) {
	    // Sample: class: 'i am a class'  'i am an alias'
	    $class_name = '';
	    $alias_name = '';
	    
	    $class_name = trim(strstr(substr(strstr($line, "'"), 1), "'", true));
	    
	    $alias_name = substr(strstr(substr(strstr($line, "'"), 1), "'"), 1);
	    $alias_name = trim(strstr(substr(strstr($alias_name, "'"), 1), "'", true));
	    
	    $this->builder->addClass($class_name, $alias_name);
	}
	
	
	// add Message
	else if ($this->contains_substr($line, '-->>*')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $origin_name = trim(strstr($line, "-", true));
	    $destination_name = trim(strstr(substr(strstr($line, ">>*"), 3), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, true, false, 1);
	}
	else if ($this->contains_substr($line, '-->*')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $origin_name = trim(strstr($line, "-", true));
	    $destination_name = trim(strstr(substr(strstr($line, ">*"), 2), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, true, true, 1);
	}
	else if ($this->contains_substr($line, '->>*')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $origin_name = trim(strstr($line, "-", true));
	    $destination_name = trim(strstr(substr(strstr($line, ">>*"), 3), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, false, false, 1);
	}
	else if ($this->contains_substr($line, '->*')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $origin_name = trim(strstr($line, "-", true));
	    $destination_name = trim(strstr(substr(strstr($line, ">*"), 2), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, false, true, 1);
	}
	
	
	
	
	else if ($this->contains_substr($line, '-->>+')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $origin_name = trim(strstr($line, "-", true));
	    $destination_name = trim(strstr(substr(strstr($line, ">>+"), 3), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, true, false, 0, false, 1);
	}
	else if ($this->contains_substr($line, '-->+')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $origin_name = trim(strstr($line, "-", true));
	    $destination_name = trim(strstr(substr(strstr($line, ">+"), 2), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, true, true, 0, false, 1);
	}
	else if ($this->contains_substr($line, '->>+')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $origin_name = trim(strstr($line, "-", true));
	    $destination_name = trim(strstr(substr(strstr($line, ">>+"), 3), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, false, false, 0, false, 1);
	}
	else if ($this->contains_substr($line, '->+')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $origin_name = trim(strstr($line, "-", true));
	    $destination_name = trim(strstr(substr(strstr($line, ">+"), 2), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, false, true, 0, false, 1);
	}
	
	
	
	else if ($this->contains_substr($line, '-->>x')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $origin_name = trim(strstr($line, "-", true));
	    $destination_name = trim(strstr(substr(strstr($line, ">>x"), 3), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, true, false, 0, false, 2);
	}
	else if ($this->contains_substr($line, '-->x')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $origin_name = trim(strstr($line, "-", true));
	    $destination_name = trim(strstr(substr(strstr($line, ">x"), 2), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, true, true, 0, false, 2);
	}
	else if ($this->contains_substr($line, '->>x')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $origin_name = trim(strstr($line, "-", true));
	    $destination_name = trim(strstr(substr(strstr($line, ">>x"), 3), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, false, false, 0, false, 2);
	}
	else if ($this->contains_substr($line, '->x')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $origin_name = trim(strstr($line, "-", true));
	    $destination_name = trim(strstr(substr(strstr($line, ">x"), 2), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, false, true, 0, false, 2);
	}
	
	
	
	
	else if ($this->contains_substr($line, '*-->>')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $origin_name = trim(strstr($line, "*-", true));
	    $destination_name = trim(strstr(substr(strstr($line, ">>"), 2), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, true, false, 2);
	}
	else if ($this->contains_substr($line, '*-->')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $origin_name = trim(strstr($line, "*-", true));
	    $destination_name = trim(strstr(substr(strstr($line, ">"), 1), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, true, true, 2);
	}
	else if ($this->contains_substr($line, '*->')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $origin_name = trim(strstr($line, "*-", true));
	    $destination_name = trim(strstr(substr(strstr($line, ">"), 1), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, false, true, 2);
	}
	else if ($this->contains_substr($line, '-->>')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $origin_name = trim(strstr($line, "--", true));
	    $destination_name = trim(strstr(substr(strstr($line, ">>"), 2), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, true, false);
	}
	else if ($this->contains_substr($line, '->>')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $origin_name = trim(strstr($line, "-", true));
	    $destination_name = trim(strstr(substr(strstr($line, ">>"), 2), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, false, false);
	}
	else if ($this->contains_substr($line, '-->')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $origin_name = trim(strstr($line, "-", true));
	    $destination_name = trim(strstr(substr(strstr($line, ">"), 1), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, true);
	}
	else if ($this->contains_substr($line, '->')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $origin_name = trim(strstr($line, "-", true));
	    $destination_name = trim(strstr(substr(strstr($line, ">"), 1), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text);
	}
	else if ($this->contains_substr($line, '<<--*')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $destination_name = trim(strstr($line, "<<", true));
	    $origin_name = trim(strstr(substr(strstr(substr(strstr($line, "-"), 1), '-*'), 2), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, true, false, 2);
	}
	else if ($this->contains_substr($line, '<--*')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $destination_name = trim(strstr($line, "<", true));
	    $origin_name = trim(strstr(substr(strstr(substr(strstr($line, "-"), 1), '-*'), 2), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, true, true, 2);
	}
	else if ($this->contains_substr($line, '<-*')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $destination_name = trim(strstr($line, "<", true));
	    $origin_name = trim(strstr(substr(strstr(substr(strstr($line, "-"), 1), '*'), 1), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, true, true, 2);
	}
	
	else if ($this->contains_substr($line, '*<<--')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $destination_name = trim(strstr($line, "*<<", true));
	    $origin_name = trim(strstr(substr(strstr(substr(strstr($line, "-"), 1), '-'), 1), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, true, false, 1);
	}
	else if ($this->contains_substr($line, '*<<--')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $destination_name = trim(strstr($line, "*<<", true));
	    $origin_name = trim(strstr(substr(strstr($line, "-"), 1), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, true, false, 1);
	}
	else if ($this->contains_substr($line, '*<--')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $destination_name = trim(strstr($line, "*<", true));
	    $origin_name = trim(strstr(substr(strstr(substr(strstr($line, "-"), 1), '-'), 1), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, true, true, 1);
	}
	else if ($this->contains_substr($line, '*<-')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $destination_name = trim(strstr($line, "*<", true));
	    $origin_name = trim(strstr(substr(strstr($line, "-"), 1), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, false, true, 1);
	}
	else if ($this->contains_substr($line, '+<<--')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $destination_name = trim(strstr($line, "+<<", true));
	    $origin_name = trim(strstr(substr(strstr(substr(strstr($line, "-"), 1), '-'), 1), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, true, false, 0, false, 1);
	}
	else if ($this->contains_substr($line, '+<<-')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $destination_name = trim(strstr($line, "+<<", true));
	    $origin_name = trim(strstr(substr(strstr($line, "-"), 1), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, false, false, 0, false, 1);
	}
	else if ($this->contains_substr($line, '+<--')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $destination_name = trim(strstr($line, "+<", true));
	    $origin_name = trim(strstr(substr(strstr(substr(strstr($line, "-"), 1), '-'), 1), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, true, true, 0, false, 1);
	}
	else if ($this->contains_substr($line, '+<-')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $destination_name = trim(strstr($line, "+<", true));
	    $origin_name = trim(strstr(substr(strstr($line, "-"), 1), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, false, true, 0, false, 1);
	}
	else if ($this->contains_substr($line, 'x<<--')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $destination_name = trim(strstr($line, "x<<", true));
	    $origin_name = trim(strstr(substr(strstr(substr(strstr($line, "-"), 1), '-'), 1), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, true, false, 0, false, 2);
	}
	else if ($this->contains_substr($line, 'x<<-')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $destination_name = trim(strstr($line, "x<<", true));
	    $origin_name = trim(strstr(substr(strstr($line, "-"), 1), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, false, false, 0, false, 2);
	}
	else if ($this->contains_substr($line, 'x<--')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $destination_name = trim(strstr($line, "x<", true));
	    $origin_name = trim(strstr(substr(strstr(substr(strstr($line, "-"), 1), '-'), 1), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, true, true, 0, false, 2);
	}
	else if ($this->contains_substr($line, 'x<-')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $destination_name = trim(strstr($line, "x<", true));
	    $origin_name = trim(strstr(substr(strstr($line, "-"), 1), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, false, true, 0, false, 2);
	}
	else if ($this->contains_substr($line, '<<--')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $destination_name = trim(strstr($line, "<<", true));
	    $origin_name = trim(strstr(substr(strstr($line, "--"), 2), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, true, false);
	}
	else if ($this->contains_substr($line, '<<-')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $destination_name = trim(strstr($line, "<<", true));
	    $origin_name = trim(strstr(substr(strstr($line, "-"), 1), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, false, false);
	}
	else if ($this->contains_substr($line, '<--')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $destination_name = trim(strstr($line, "<", true));
	    $origin_name = trim(strstr(substr(strstr(substr(strstr($line, "-"), 1), '-'), 1), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text, true);
	}
	else if ($this->contains_substr($line, '<-')) {
	    // Sample: class a -> class b : message
	    $origin_name = '';
	    $destination_name = '';
	    $message_text = '';	    
	    
	    $destination_name = trim(strstr($line, "<", true));
	    $origin_name = trim(strstr(substr(strstr($line, "-"), 1), ':', true));
	    $message_text = trim(substr(strstr($line, ':'), 1));
	    
	    $this->builder->addMessage($origin_name, $destination_name, $message_text);
	}
	// Start Activity
	else if ($this->contains_substr($line, 'start:')) {
	    $class_name = trim(substr($line, 6));
	    $this->builder->addMessage($class_name, $class_name, '', false, false, 1, true);
	}
	// Stop Activity
	else if ($this->contains_substr($line, 'stop:')) {
	    $class_name = trim(substr($line, 6));
	    $this->builder->addMessage($class_name, $class_name, '', false, false, 2, true);
	}
	// Title
	else if ($this->contains_substr($line, 'title:')) {
	    $title = trim(substr($line, 7));
	    $this->builder->setTitle($title);
	}
	// Note
	else if ($this->contains_substr($line, 'note:')) {
	    $note = trim(substr($line, 6));
	    $this->builder->addNote($note);
	}
	// Start block
	else if ($this->contains_substr($line, 'open:')) {
	    $title = '';
	    $text = '';
	    
	    $title = trim(strstr(substr(strstr($line, "'"), 1), "'", true));
	    
	    $text = substr(strstr(substr(strstr($line, "'"), 1), "'"), 1);
	    $text = trim(strstr(substr(strstr($text, "'"), 1), "'", true));
	    
	    $this->builder->addBlock($title, $text);
	}
	// Split block
	else if ($this->contains_substr($line, 'split:')) {
	    $text = '';
	    
	    $text = trim(strstr(substr(strstr($line, "'"), 1), "'", true));
	    
	    $this->builder->splitBlock($text);
	}
	// End block
	else if ($this->contains_substr($line, 'close')) {
	    $this->builder->endBlock();
	}
	// Ref block
	else if ($this->contains_substr($line, 'ref:')) {
	    $title = '';
	    
	    $title = trim(strstr(substr(strstr($line, "'"), 1), "'", true));
	    
	    $this->builder->addBlock('REF', $title, true);
	    $this->builder->endBlock();
	}
	// space
	else if ($this->contains_substr($line, 'space')) {
	    $this->builder->addNote('', true);
	}
	else {
	    $this->errors[] = 'Syntax Error in line '.($id + 1).'.';
	}
    }
}

?>