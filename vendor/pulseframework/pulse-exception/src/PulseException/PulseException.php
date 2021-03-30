<?php
/**
*    2015 S O F T I N T H E B O X
*
* NOTICE OF LICENSE
*
* It is also available through the world-wide-web at this URL:
* http://www.pulseframework.com/developer-platform
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to hello@pedroteixeira.pro so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this to newer
* versions in the future. If you wish to customize this for your
* needs please refer to http://www.pulseframework.com for more information.
*
*  @author Pedro Teixeira - Pulse Framework <me@pedroteixeira.pro>
*  @copyright  2015 Pulse Framework
*  @license    http://www.pulseframework.com/license
*  International Registered Trademark & Property of Pulse Framework
*/

namespace PulseException;

use Exception;

if (!defined('_ROOT_DIR_')) {
  if( isset($_SERVER['DOCUMENT_ROOT']) )
    define('_ROOT_DIR_', $_SERVER['DOCUMENT_ROOT'] );
  else
    define('_ROOT_DIR_', '' );
}

if (!defined('_MODE_DEV_')) {
  define('_MODE_DEV_', false );
}

class PulseException extends Exception
{
  const CRITICAL = 1;
  const ERROR = 2;
  const WARNING = 3;
  const INFO = 4;

  public function displayMessage()
  {
    if( $this->code <= self::ERROR ){
      header('HTTP/1.1 500 Internal Server Error');
    }

    if(_MODE_DEV_){
      echo '<style>
        body{ background: #f9f9f9; margin: 0; font-family: monospace;}
        header{ width: 100%; height: 70px; text-align: center; padding: 15px; font-size: 28px; font-weight: 600; box-sizing: border-box; color: #fff}
        header span { font-size: 25px; font-weight: 300; }
        #pulseException{font-family: Verdana; font-size: 14px; width: 800px; background: #fff; padding: 20px; margin: auto; margin-top: 30px; border: solid 1px #cecece;}
        #pulseException h2{color: #F20000}
        #pulseException h3{color: #000000}
        #pulseException p{padding-left: 20px}
        #pulseException ul li{margin-bottom: 10px}
        #pulseException a{font-size: 12px; color: #000000}
        #pulseException .psTrace, #pulseException .psArgs{display: none}
        #pulseException pre{border: 1px solid #236B04; background-color: #EAFEE1; padding: 5px; font-family: Courier; width: 99%; overflow-x: auto; margin-bottom: 30px;}
        #pulseException .psArgs pre{background-color: #F1FDFE;}
        #pulseException pre .selected{color: #F20000; font-weight: bold;}
      </style>';

      echo $this->getMessageHeader();

      echo '<div id="pulseException">';
      echo '<h3>[' . get_class($this) . ']</h3>';
      echo '<h2>' . $this->getMessage() . '</h2>';

      echo $this->getExtendedMessage();

      // Display debug backtrace
      echo '<ul>';
      foreach ($this->getTrace() as $id => $trace) {
        $relative_file = (isset($trace['file'])) ? ltrim(str_replace(array(_ROOT_DIR_, '\\'), array('', '/'), $trace['file']), '/') : '';
        $current_line = (isset($trace['line'])) ? $trace['line'] : '';

        echo '<li>';
        echo '<b>' . ((isset($trace['class'])) ? $trace['class'] : '') . ((isset($trace['type'])) ? $trace['type'] : '') . $trace['function'] . '</b>';
        echo ' - <a style="font-size: 12px; color: #000000; cursor:pointer; color: blue;" onclick="document.getElementById(\'psTrace_' . $id . '\').style.display = (document.getElementById(\'psTrace_' . $id . '\').style.display != \'block\') ? \'block\' : \'none\'; return false">[line ' . $current_line . ' - ' . $relative_file . ']</a>';

        if (isset($trace['args']) && count($trace['args'])) {
          echo ' - <a style="font-size: 12px; color: #000000; cursor:pointer; color: blue;" onclick="document.getElementById(\'psArgs_' . $id . '\').style.display = (document.getElementById(\'psArgs_' . $id . '\').style.display != \'block\') ? \'block\' : \'none\'; return false">[' . count($trace['args']) . ' Arguments]</a>';
        }

        echo '</li>';
      }
      echo '</ul>';

      echo '</div>';
    }else{
      // If not in mode dev, display an error page
      if (file_exists(_ROOT_DIR_ . '/500.html')) {
        echo file_get_contents(_ROOT_DIR_ . '/500.html');
        exit();
      }
    }

    if( $this->code <= self::ERROR ){
      if ( !_MODE_DEV_ && file_exists(_ROOT_DIR_ . '/500.html')) {
        echo file_get_contents(_ROOT_DIR_ . '/500.html');
      }
      exit();
    }
  }

  protected function getMessageHeader($html = true)
  {
    switch ($this->code) {
      case self::CRITICAL:
        $color = 'F20000';
        break;
      case self::ERROR:
        $color = 'F20000';
        break;
      case self::WARNING:
        $color = 'ff8100';
        break;
      case self::INFO:
        $color = '0072ff';
        break;
      default:
        $color = 'F20000';
        break;
    }
    return '
      <header style="background: #'.$color.'" >
        '.($this->code == self::CRITICAL ? 'CRITICAL':'').'
        '.($this->code == self::ERROR ? 'ERROR':'').'
        '.($this->code == self::WARNING ? 'WARNING':'').'
        '.($this->code == self::INFO ? 'INFO':'').'
        : <span>'.$this->getMessage().' </span>
      </header>
    ';
  }

  protected function getExtendedMessage($html = true)
  {
    $format = '<p><i>at line </i><b>%d</b><i> in file </i><b>%s</b></p>';
    if (!$html) {
      $format = strip_tags(str_replace('<br />', ' ', $format));
    }
    return sprintf(
      $format,
      $this->getLine(),
      ltrim(str_replace(array(_ROOT_DIR_, '\\'), array('', '/'), $this->getFile()), '/')
    );
  }

}
