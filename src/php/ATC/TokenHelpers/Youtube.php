<?php
namespace ATC\TokenHelpers;

use ATC\Config;

class Youtube extends Text
{
    protected $file = 'input-youtube.html';

    public function getField() {
        $this->map['[[__help]]'] = 'Paste in your video ID or embed code from YouTube.';
        return parent::getField();
    }
}