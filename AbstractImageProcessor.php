<?php

namespace EasyE;

require_once 'Exception.php';

abstract class AbstractImageProcessor 
{
    public function __construct()
    {
        if (!extension_loaded('gd')) {
            throw new Exception('The PHP GD extension is not loaded.');
        }
    }
}