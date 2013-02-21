<?php

/**
* ------------------------------------------------------
*         Sequence Diagram Library: SeDiLib
* ------------------------------------------------------
*
* @copyright (C) 2013 Alexander Giesler (giesler.alexander@googlemail.com)
* @copyright (C) 2006 Code-Kobold (Ron Metten) (www.code-kobold.de) (function drawArrow)
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


/** Drawing layor over php gd for simplicity
* TODO: Abstract specific positions, measures etc. for objects in separate class and only provide
*       drawing methods here !
*/
class Draw {
    /** class box measures
    */
    private $class_width_offset = 20;
    private $class_height_offset = 7;
    private $class_font_size = 5;
    private $class_text_color = array(100, 0, 0);
    
    /** message measures
    */
    private $message_width_offset = 25;
    private $message_font_size = 5;
    private $message_color = array(0, 0, 0);
    
    private $message_self_width = 30;
    private $message_self_height = 10;
    private $message_self_text_offset = 10;
    
    /** Activity measures
    */
    private $activity_width = 10;
    private $activity_min_height = 50;
    private $activity_fill_color = array(255, 255, 204);
    private $activity_border_color = array(153, 0, 51);
    
    /** Colors
    */
    private $background_color = array(255, 255, 255);
    private $border_color = array(153, 0, 51);
    private $line_color = array(153, 0, 51);
    private $fill_color = array(255, 255, 204);
    
    /** Arrowhead properties
    */
    private $arrow_alpha = 20;
    private $arrow_beta = 12;
    
    /** Pens
    */
    private $background_pen;
    private $message_text_pen;
    private $class_text_pen;
    private $border_pen;
    private $line_pen;
    private $fill_pen;
    private $activity_border_pen;
    private $activity_fill_pen;
    
    /** Image object
    */
    private $im;
    
    /** Width and height of the image
    */
    private $_width;
    private $_height;
    
    /** C'tor
    */
    public function __construct() {
    }
    
    /** Creates image and initializes drawing objects like pens and colors
    */
    public function createImage($width, $height) {
	// Initalize PHP GD
	$this->im = ImageCreateTrueColor($width, $height); 
	
	$this->background_pen = ImageColorAllocate($this->im, $this->background_color[0], $this->background_color[1], $this->background_color[2]);
	
	$this->class_text_pen   = ImageColorAllocate($this->im, $this->class_text_color[0],   $this->class_text_color[1],   $this->class_text_color[2]);
	$this->message_text_pen = ImageColorAllocate($this->im, $this->message_color[0],   $this->message_color[1],   $this->message_color[2]);
	$this->border_pen = ImageColorAllocate($this->im, $this->border_color[0], $this->border_color[1], $this->border_color[2]);
	$this->line_pen   = ImageColorAllocate($this->im, $this->line_color[0],   $this->line_color[1],   $this->line_color[2]);
	$this->fill_pen   = ImageColorAllocate($this->im, $this->fill_color[0],   $this->fill_color[1],   $this->fill_color[2]);
	
	$this->activity_border_pen = ImageColorAllocate($this->im, $this->activity_border_color[0], $this->activity_border_color[1], $this->activity_border_color[2]);
	$this->activity_fill_pen = ImageColorAllocate($this->im, $this->activity_fill_color[0], $this->activity_fill_color[1], $this->activity_fill_color[2]);
	
	ImageFillToBorder($this->im, 0, 0, $this->background_pen, $this->background_pen);
	
	$this->_width = $width;
	$this->_height = $height;
    }
    
    /** Displays the image
    */
    public function display() {
	// Send the new PNG image to the browser
	ImagePNG($this->im); 

	// Destroy the reference pointer to the image in memory to free up resources
	ImageDestroy($this->im); 
    }
    
    /** Returns measures of the class box
    */
    public function classMeasures($text, &$width, &$height) {
	$width = $this->textWidth($text, $this->class_font_size) + 2 * $this->class_width_offset;
	$height = $this->textHeight($text, $this->class_font_size) + 2 * $this->class_height_offset;
    }
    
    /** Draws class lifeline
    */
    public function drawClassLifeLine($x, $y1, $y2) {
	if ($y2 == 0) {
	    $y2 = $this->_height;
	    $this->drawLine($x, $y1, $x, $y2, true);
	}
	// Draw cross indicating destruction
	else {
	    $cross_offset = 10;
	    
	    $this->drawLine($x, $y1, $x, $y2, true);
	    
	    $this->drawLine($x - $cross_offset, $y2 - $cross_offset, $x + $cross_offset, $y2 + $cross_offset, false);
	    $this->drawLine($x - $cross_offset, $y2 + $cross_offset, $x + $cross_offset, $y2 - $cross_offset, false);
	}
    }
    
    /** Draws class box
    */
    public function drawClassBox($x, $y, $width, $height, $text) {	    
	$this->drawRectangleCentered($x, $y, $width, $height);
	$this->drawTextMultiline($x, $y, $text, $this->class_font_size, $this->class_text_pen, true, true);
    }
    
    /** Returns width of a message arrow with text
    */
    public function messageWidth($text, $self_message = false) {
	if ($self_message) {
	    return $this->message_self_width + $this->message_self_text_offset + $this->textWidth($text, $this->message_font_size) + $this->message_width_offset;
	}
	else {
	    return $this->textWidth($text, $this->message_font_size) + 2 * $this->message_width_offset;
	}
    }
    
    public function messageHeight($text, $self_message = false) {
	if ($self_message) {
	    return $this->message_self_height + $this->textHeight($text, $this->message_font_size);
	}
	else {
	    return $this->textHeight($text, $this->message_font_size) + 10;
	}
    }
    
    /** Draws a message
    */
    public function drawMessage($text, $x1, $x2, $y, $dashed, $arrowHead, $self = false) {
	$width = $this->textWidth($text, $this->message_font_size) ;
	$height = $this->textHeight($text, $this->message_font_size);
	$height_one_line = $this->textHeight('placeholder', $this->message_font_size);
	    
	if (!$self) {
	    $this->drawArrow($x1, $y, $x2, $y, $arrowHead, $dashed);
	    
	    $text_x = $x1 + ( $x2 - $x1 ) / 2;
	    $text_y = $y - $height;
		    
	    // Background white
	    ImageFilledRectangle($this->im, $text_x - $width / 2, $text_y - $height_one_line / 2, $text_x + $width / 2, $text_y + $height - $height_one_line / 2, $this->background_pen);		
		    
	    $this->drawTextMultiline($text_x, $text_y, $text, $this->message_font_size, $this->message_text_pen);
	}
	// Message is directed from and to same class
	else {
	    $x_offset = $this->message_self_width;
	    $y_offset = $this->message_self_height + $height;
	    	    
	    $this->drawLine($x1, $y, $x1 + $x_offset, $y, $dashed);
	    $this->drawLine($x1 + $x_offset, $y, $x1 + $x_offset, $y + $y_offset, $dashed);
	    $this->drawArrow($x1 + $x_offset, $y + $y_offset, $x2, $y + $y_offset, $arrowHead, $dashed);
	    
	    
	    $text_x = $x1 + $x_offset + $this->message_self_text_offset;
	    $text_y = $y + ($y_offset / 2) - ($height / 2);
		    
	    // Background white
	    ImageFilledRectangle($this->im, $text_x, $text_y, $text_x + $width, $text_y + $height, $this->background_pen);		
		    
	    $this->drawTextMultiline($text_x, $text_y, $text, $this->message_font_size, $this->message_text_pen, false);
	}
    }
    
    /** Draws an activity box
    */
    public function drawActivity($x, $y1, $y2) {
	ImageFilledRectangle($this->im, $x - $this->activity_width / 2, $y1, $x + $this->activity_width / 2, $y2, $this->activity_fill_pen);
	ImageRectangle($this->im, $x - $this->activity_width / 2, $y1, $x + $this->activity_width / 2, $y2, $this->activity_border_pen);
    }
        
    /** Returns width of a text
    */
    private function textWidth($text, $font_size) {
	$lines = explode('\n', $text);
	$max_x = -1;
	
	for ($i = 0; $i < count($lines); $i++) {
	    $current_width = ImageFontWidth($font_size) * strlen($lines[$i]);
	    
	    if ($current_width > $max_x) {
		$max_x = $current_width;
	    }
	}
    
    
	return $max_x;//ImageFontWidth($font_size) * strlen($text);
    }
    
    /** Returns height of a text
    */
    private function textHeight($text, $font_size) {
	$lines = substr_count($text, '\n') + 1; 
	return ImageFontHeight($font_size) * $lines;
    }
    
    /** Draws a text
    */
    private function drawText($x, $y, $text, $font_size, $font_color, $centered = true, $underlined = false) {
	if ($centered) {
	    $width = $this->textWidth($text, $font_size);
	    $height = $this->textHeight($text, $font_size);
	    
	    $x = $x - $width / 2;
	    $y = $y - $height / 2;
	}
	
	ImageString($this->im, $font_size, $x, $y, $text, $font_color ); 
	
	if ($underlined) {
	    $width = $this->textWidth($text, $font_size);
	    $height = $this->textHeight($text, $font_size);
	    
	    $this->drawLine($x - 2, $y + $height + 1, $x + $width + 1, $y + $height + 1);
	}
    }
    
    private function drawTextMultiline($x, $y, $text, $font_size, $font_color, $centered = true, $underlined = false) {
	$lines = explode('\n', $text);
	$y_inc = $this->textHeight($text, $font_size) / count($lines);
	
	for ($i = 0; $i < count($lines); $i++) {
	   $this->drawText($x, $y + $i * $y_inc, $lines[$i], $font_size, $font_color, $centered, $underlined);
	}
    }
    
    /** Draws a rectangle centered in x and y
    */
    private function drawRectangleCentered($x, $y, $width, $height, $color = NULL) {	    
	ImageFilledRectangle($this->im, $x - $width / 2, $y - $height / 2, $x + $width / 2, $y + $height / 2, $this->fill_pen);
	ImageRectangle($this->im, $x - $width / 2, $y - $height / 2, $x + $width / 2, $y + $height / 2, $this->border_pen);
    }
	    
    /** Draws a line
    */
    private function drawLine($x1, $y1, $x2, $y2, $dashed = false) {
	if ($dashed == true) {
	    imageantialias($this->im, false);
	    
	    $style = array($this->line_pen, $this->line_pen, $this->line_pen, $this->line_pen, $this->line_pen, $this->background_pen, $this->background_pen, $this->background_pen, $this->background_pen, $this->background_pen);
	    
	    imagesetstyle($this->im, $style);
	    
	    Imageline($this->im, $x1, $y1, $x2, $y2, IMG_COLOR_STYLED);
	    imageantialias($this->im, true);
	}
	else {
	    Imageline($this->im, $x1, $y1, $x2, $y2, $this->line_pen);
	}
    }
    
    /**
    * Draws an Arrow from x1,y1 to x2,y2 using the gd-library.
    * The Arrowheads always point to x2,y2
    *
    * @copyright (C) 2006 Code-Kobold (Ron Metten) (www.code-kobold.de)
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
    *
    * @author Code-Kobold (Ron Metten) (www.code-kobold.de)
    * @since 2006 Dec 02 (Reworked 2009 Aug 27)
    * 
    * @param image (The referenced canvas)
    * @param color (GD color of the arrow)
    * @param x1, y1, x2, y2 (Starting and endpoint of the arrow)
    * @param angle (Arm angle of the arrowhead)
    * @param radius (Length of the arrowhead)
    * 
    * @modified 2013-02-10 to support dashed line and filled arrow head
    */
    private function drawArrow($x1, $y1, $x2, $y2, $filledHead = false, $dashed = false) {
	$l_m = null;
	$l_x1 = null;
	$l_y1 = null;
	$l_x2 = null;
	$l_y2 = null;
	$l_angle1 = null;
	$l_angle2 = null;
	$l_cos1 = null;
	$l_sin1 = null;$black = ImageColorAllocate($this->im, 0, 0, 0);

	// Draws the arrow's line
	if ($dashed == true) {
	    imageantialias($this->im, false);
	    $style = array($this->line_pen, $this->line_pen, $this->line_pen, $this->line_pen, $this->line_pen, $this->background_pen, $this->background_pen, $this->background_pen, $this->background_pen, $this->background_pen);
	    
	    imagesetstyle($this->im, $style);
	    
	    Imageline($this->im, $x1, $y1, $x2, $y2, IMG_COLOR_STYLED);
	    imageantialias($this->im, true);

	}
	else {
	    Imageline($this->im, $x1, $y1, $x2, $y2, $this->line_pen);
	}
	
	// Gradient infinite?
	if ($x2 == $x1) {
	    $l_m = FALSE;
	    
	    if ($y1 < $y2) {
		$l_x1 = $x2 - $this->arrow_beta * sin(deg2rad($this->arrow_alpha));
		$l_y1 = $y2 - $this->arrow_beta * cos(deg2rad($this->arrow_alpha));
		$l_x2 = $x2 + $this->arrow_beta * sin(deg2rad($this->arrow_alpha));
		$l_y2 = $y2 - $this->arrow_beta * cos(deg2rad($this->arrow_alpha));
	    } else {
		$l_x1 = $x2 - $this->arrow_beta * sin(deg2rad($this->arrow_alpha));
		$l_y1 = $y2 + $this->arrow_beta * cos(deg2rad($this->arrow_alpha));
		$l_x2 = $x2 + $this->arrow_beta * sin(deg2rad($this->arrow_alpha));
		$l_y2 = $y2 + $this->arrow_beta * cos(deg2rad($this->arrow_alpha));
	    }
	} 
	// Gradient = 0
	elseif ($y2 == $y1) {
	    $l_m = 0;

	    if ($x1 < $x2) {
		$l_x1 = $x2 - $this->arrow_beta * cos(deg2rad($this->arrow_alpha));
		$l_y1 = $y2 - $this->arrow_beta * sin(deg2rad($this->arrow_alpha));
		$l_x2 = $x2 - $this->arrow_beta * cos(deg2rad($this->arrow_alpha));
		$l_y2 = $y2 + $this->arrow_beta * sin(deg2rad($this->arrow_alpha));
	    } else {
		$l_x1 = $x2 + $this->arrow_beta * cos(deg2rad($this->arrow_alpha));
		$l_y1 = $y2 + $this->arrow_beta * sin(deg2rad($this->arrow_alpha));
		$l_x2 = $x2 + $this->arrow_beta * cos(deg2rad($this->arrow_alpha));
		$l_y2 = $y2 - $this->arrow_beta * sin(deg2rad($this->arrow_alpha));
	    }
	} 
	// Gradient positive?
	elseif ($x2 > $x1) {
	    // Calculate gradient
	    $l_m = (($y2 - $y1) / ($x2 - $x1));

	    // Convert gradient (= Arc tangent(m)) from radian to degree
	    $l_alpha = rad2deg(atan($l_m));

	    // Right arm angle = gradient + 180 + arm angle
	    $l_angle1 = $l_alpha + $this->arrow_alpha + 180;
	    // Left arm angle = gradient + 180 - arm angle
	    $l_angle2 = $l_alpha - $this->arrow_alpha + 180;

	    // Right arm angle of arrowhead
	    // Abscissa = cos(gradient + 180 + arm angle) * radius
	    $l_cos1 = $this->arrow_beta * cos(deg2rad($l_angle1));
	    $l_x1 = $x2 + $l_cos1;

	    // Ordinate = sin(gradient + 180 + arm angle) * radius
	    $l_sin1 = $this->arrow_beta * sin(deg2rad($l_angle1));
	    $l_y1 = $y2 + $l_sin1;

	    // Left arm angle of arrowhead
	    $RCos2 = $this->arrow_beta * cos(deg2rad($l_angle2));
	    $RSin2 = $this->arrow_beta * sin(deg2rad($l_angle2));

	    $l_x2 = $x2 + $RCos2;
	    $l_y2 = $y2 + $RSin2;
	}
	// Gradient negative?
	elseif ($x2 < $x1) {
	    $this->arrow_alpha = 90 - $this->arrow_alpha;

	    // Calculate gradient
	    $l_m = (($y2 - $y1) / ($x2 - $x1));

	    // Convert gradient (= Arc tangent(m)) from radian to degree
	    $l_alpha = rad2deg(atan($l_m));

	    // Right arm angle = gradient + 180 + arm angle
	    $l_angle1 = $l_alpha + $this->arrow_alpha + 180;
	    // Left arm angle = gradient + 180 - arm angle
	    $l_angle2 = $l_alpha - $this->arrow_alpha + 180;

	    // Right arm angle of arrowhead
	    // Abscissa = cos(gradient + 180 + arm angle) * radius
	    $l_cos1 = $this->arrow_beta * cos(deg2rad($l_angle1));

	    // Ordinate = sin(gradient + 180 + arm angle) * radius
	    $l_sin1 = $this->arrow_beta * sin(deg2rad($l_angle1));

	    // Left arm angle of arrowhead
	    $RCos2 = $this->arrow_beta * cos(deg2rad($l_angle2));
	    $RSin2 = $this->arrow_beta * sin(deg2rad($l_angle2));

	    $l_x1 = $x2 - $l_sin1;
	    $l_y1 = $y2 + $l_cos1;

	    $l_x2 = $x2 + $RSin2;
	    $l_y2 = $y2 - $RCos2;
	}
	
	if ($filledHead) {
	  $points = array($l_x1, $l_y1, $x2, $y2, $l_x2, $l_y2);
	  imagefilledpolygon($this->im, $points, 3, $this->line_pen);
	}
	else {
	  Imageline($this->im, $l_x1, $l_y1, $x2, $y2, $this->line_pen);
	  Imageline($this->im, $l_x2, $l_y2, $x2, $y2, $this->line_pen);
	}
    }

}

?>