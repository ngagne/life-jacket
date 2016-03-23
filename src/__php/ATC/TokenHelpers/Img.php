<?php
namespace ATC\TokenHelpers;

use ATC\Config;

class Img extends AbstractTokenHelper
{
    protected $file = 'input-file.html';

    public function getField() {
        $this->map['[[__img]]'] = '';
        if ($this->value != '') {
            $imgMap = $this->map;
            $imgMap['[[__value]]'] = $this->getString();

            // load image thumbnail
            $img = $this->render('input-img.html', $imgMap);
            $this->map['[[__img]]'] = $img;
        }

        return parent::getField();
    }

    public function getString() {
        $config = Config::getInstance();
        if ($this->value != '') {
            return '/' . trim($config->image_uploads_path, '/') . '/' . str_replace('/', '~~', $this->value);
        }

        return '';
    }
}