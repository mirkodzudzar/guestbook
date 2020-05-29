<?php

namespace App;

use Imagine\Image\Box;
use Imagine\Gd\Imagine;

class ImageOptimizer
{
  private const MAX_WIDTH = 200;
  private const MAX_HEIGHT = 150;

  private $imagine;

  public function __construct()
  {
    $this->imagine = new Imagine();
  }

  public function resize(string $filname): void
  {
    list($iwidth, $iheight) = getimagesize($filename);
    $ration = $iwidth / $iheight;
    $width = self::MAX_WIDTH;
    $height = self::MAX_HEIGHT;

    if ($width / $height > $ratio) {
      $width = $height * $ratio;
    } else {
      $height = $width / $ratio;
    }

    $photo = $this->imagine->open($filname);
    $photo->resize(new Box($width, $height))->save($filename);
  }
}