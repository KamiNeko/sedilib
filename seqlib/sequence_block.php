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

require_once('sequence_object.php');
require_once('sequence_sub_block.php');

/** Represents a block in the sequence diagram (for example loop-block)
*/
class SequenceBlock extends SequenceObject {
    /** Array of inner sub blocks, e.g., the inner options in if/else case
    */
    private $_innerSubBlocks = array();
    
    private $_emptyFill = FALSE;
    
    public function emptyFill() {
	return $this->_emptyFill;
    }
    
    public function setEmptyfill($fill) {
	$this->_emptyFill = $fill;
    }
    
    /** Reference to parent sub block
    */
    private $_parent = NULL;
    
    public function __construct() {

    }
    
    public function innerSubBlocks() {
	return $this->_innerSubBlocks;
    }
    
    public function addInnerSubBlock($block) {
	$this->_innerSubBlocks[] = $block;
    }
    
    public function parentPtr() {
	return $this->_parent;
    }
    
    public function setParentPtr($parent) {
	$this->_parent = $parent;
    }
    
    public function draw($draw) {
	// Draw title
	$first_sub_block = $this->_innerSubBlocks[0];
	
	foreach ($this->_innerSubBlocks as $subBlock) {
	    $subBlock->draw($draw);
	}
	
	$draw->drawBlock($first_sub_block->x1(), $first_sub_block->y1(), $this->_name);
		
    }
    
    public function setWidth($width, $draw = NULL) {
	if ($width == $this->_width) {
	    return;
	}
	
	$this->_width = $width;
	
	$max_width_children = -1;
	
	foreach ($this->_innerSubBlocks as $block) {
	    $block->setWidth($width, $draw);
	    
	    if ($block->width() > $max_width_children) {
		$max_width_children = $block->width();
	    }
	    
	    if ($draw->minBlockWidth($this->_name, $block->text()) > $max_width_children) {
		$max_width_children = $draw->minBlockWidth($this->_name, $block->text());
	    }
	}
	
	$this->_width = $max_width_children;
	
	foreach ($this->_innerSubBlocks as $block) {
	    $block->setWidthNonRecursive($max_width_children, $draw);
	}
    }
    
    public function adjustX($centerX) {
	foreach ($this->_innerSubBlocks as $block) {
	    $block->adjustX($centerX);
	}
    }
}

?>