<?php

namespace EasyE;

require_once 'Exception.php';

abstract class AbstractImageProcessor 
{
    private $_sourceFileLocation = null;
    private $_destinationFileLocation = null;

    public function __construct($sourceFileLocation = null, 
                                    $destinationFileLocation = null)
    {
        if (!extension_loaded('gd')) {
            throw new Exception('The PHP GD extension is not loaded.');
        }

        if (!is_null($sourceFileLocation)){
            $this->setSourceFileLocation($sourceFileLocation);
        }

        if (!is_null($destinationFileLocation)){
            $this->setDestinationFileLocation($destinationFileLocation);
        }
    }

    public function setSourceFileLocation($fileLocation)
    {
        if( (!is_file($fileLocation)) || (!file_exists($fileLocation)) ){
            throw new Exception('Source file does not exist.');
        }elseif(!is_readable($fileLocation)){
            throw new Exception('Source file is not readable.');
        }

        $this->_sourceFileLocation = $fileLocation;

        return $this;
    }

    public function setDestinationFileLocation($fileLocation)
    {
        $dir = dirname($fileLocation);

        if(!is_dir($dir) ){
            throw new Exception('Destination directory (' . $dir . ') does not exist.');
        }elseif(!is_writable($dir)){
            throw new Exception('Destination directory (' . $dir . ') is not writable.');
        }

        $this->_destinationFileLocation = $fileLocation;
        
        return $this;
    }

    public function getSourceFileLocation()
    {
        if (is_null($this->_sourceFileLocation)){
            throw new Exception('Source file location not set');
        }

        return $this->_sourceFileLocation;
    }

    public function getDestinationFileLocation()
    {
        if (is_null($this->_destinationFileLocation)){
            return $this->getSourceFileLocation();
        }
        
        return $this->_destinationFileLocation;
    }
}