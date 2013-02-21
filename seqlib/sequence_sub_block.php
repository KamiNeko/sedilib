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

/** Represents a sub block in the sequence diagram (for example a specific case in if / else block)
*/
class SequenceSubBlock extends SequenceObject {
    /** An array of stacked blocks
    */
    private $_innerBlocks = array();
    
    /** Reference to parent block
    */
    private $_parent = NULL;
    
    public function __construct() {

    }
    
    public function innerBlocks() {
	return $this->_innerBlocks;
    }
    
    public function addInnerBlock($block) {
	$this->_innerBlocks[] = $block;
    }
    
    public function parentPtr() {
	return $this->_parent;
    }
    
    public function setParentPtr($parent) {
	$this->_parent = $parent;
    }
    
    public function draw($draw) {
	$draw->drawSubBlock($this->_x1, $this->_y1, $this->_x2, $this->_y2, $this->_text, $this->_parent->emptyFill());
    
    
	foreach ($this->_innerBlocks as $block) {
	    $block->draw($draw);
	}
    }
    
    public function setWidth($width, $draw = NULL) {	
	if ($width == $this->_width) {
	    return;
	}
	
	$this->_width = $width;
	
	$max_width_children = -1;
	
	foreach ($this->_innerBlocks as $block) {
	    $block->setWidth($width, $draw);
	    
	    if ($block->width() > $max_width_children) {
		$max_width_children = $block->width();
	    }
	}
	
	if ($max_width_children > 0) {
	    $this->_width = $max_width_children + 30;
	}
    }
    
    public function setWidthNonRecursive($width, $draw) {
	$this->_width = $width;
    }
    
    public function adjustX($centerX) {
	$this->_x1 = $centerX - $this->_width / 2;
	$this->_x2 = $centerX + $this->_width / 2;
		
	foreach ($this->_innerBlocks as $block) {
	    $block->adjustX($centerX);
	}
    }
}

?>