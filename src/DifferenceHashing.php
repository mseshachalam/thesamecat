<?php

namespace mseshachalam\TheSameCat;

/**
 * DifferenceHashing(https://news.ycombinator.com/item?id=2614797)
 * Refer http://blog.iconfinder.com/detecting-duplicate-images-using-python/
 * @author abbiya@gmail.com
 */
use Intervention\Image\ImageManagerStatic as Image;

class DifferenceHashing implements DifferenceHashingInterface
{

    private $imagePath;
    private $interventionImage;
    private $width;
    private $height;
    private $commonHeight = 8;
    private $commonWidth = 9;
    private $pixels;
    private $difference;
    private $binaryStr;
    private $hexStr;

    public function __construct($image)
    {
        $this->imagePath = $image;
        $this->interventionImage = Image::make($image);
        $this->width = $this->interventionImage->width();
        $this->height = $this->interventionImage->height();
        $this->pixels = array();
        $this->difference = array();
    }

    public function compareAdjacentPixels()
    {
        $resource = imagecreatefromstring($this->interventionImage->encode()->getEncoded());
        $wRange = range(0, $this->width - 1);
        $hRange = range(0, $this->height - 1);

        foreach($wRange as $row) {
            foreach($hRange as $col) {
                $colorIndex = imagecolorat($resource, $row, $col);
                $r = ($colorIndex >> 16) & 0xFF;
                $this->pixels[$row][$col] = $r;
            }
        }
        foreach($hRange as $row) {
            foreach($wRange as $col) {
                if($col != $this->width) {
                    $this->difference[$row][$col] = $this->pixels[$row] > $this->pixels[$col];
                }
            }
        }
        
        return $this;
    }

    public function compareHashes($hash1, $hash2)
    {
        return $hash1 === $hash2;
    }

    public function convertDifferenceIntoBits()
    {
        $this->binaryStr = '';
        foreach($this->difference as $diff) {
            foreach($diff as $d) {
                $this->binaryStr .= (int)$d;
            }
        }
        
        $this->hexStr = dechex(bindec($this->binaryStr));
        
        return $this->hexStr;
    }

    public function greyScaleImage()
    {
        $this->interventionImage->greyscale();

        return $this;
    }

    public function openImage()
    {
        $this->interventionImage = Image::make($this->imagePath);

        return $this;
    }

    public function shrinkImageToCommonSize()
    {
        $this->interventionImage->resize($this->commonWidth, $this->commonHeight);
        $this->width = $this->interventionImage->width();
        $this->height = $this->interventionImage->height();

        return $this;
    }

}