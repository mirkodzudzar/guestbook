<?php

namespace App\Services;

use App\Services\FileUploader;

class Notification
{
  private $email;

  public function __construct($email)
  {
    $this->email = $email;
  }

  public function sendNotification()
  {

  }
}