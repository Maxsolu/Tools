<?php

namespace Ack\Foundation\Tools;

class Image {
	private $imagine;
	private $mode;
	private $image;
    private $width;
    private $height;
    private $imageResized;
	function __construct($fileName){
		if (!is_file($fileName)){
			return false;
        }
		$this->mode    = \Imagine\Image\ImageInterface::THUMBNAIL_INSET;
		$this->imagine = new \Imagine\Gd\Imagine();
		$this->image  = $this->imagine->open($fileName);
		$wSize = $this->image->getSize();
		$this->width= $wSize->getWidth();
		$this->height= $wSize->getHeight();
		
    }
	public function resize($newWidth, $newHeight, $background='#FFFFFF',$opacity=100,$option='auto'){
			if((int)$newWidth > 0 and (int) $newHeight >0){
					$newsize  = new \Imagine\Image\Box($newWidth, $newHeight);
					$palette = new \Imagine\Image\Palette\RGB();	
					$color = $palette->color($background, $opacity);
					$this->imageResized = $this->imagine->create($newsize, $color);
					$thumbnail = $this->image->thumbnail($newsize, $this->mode);
					$size    = $thumbnail->getSize();
					$bottomRight = new \Imagine\Image\Point(round(($newWidth - $size->getWidth())/2), round(($newHeight- $size->getHeight())/2));
					$this->imageResized->paste($thumbnail, $bottomRight);

			}
		   
	}
	public function save($savePath, $imageQuality="100"){
		 $this->imageResized->save($savePath);
	}
	
	
}

