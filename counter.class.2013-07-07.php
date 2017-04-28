<?php
/**
 * zeList Counter Class
 * Profiling purposes 
 *
 * @package zeList
 * @subpackage Plugin
 */

class counter { 
	// variables
	var $markers = array();
	var $counters = array();
	var $holders = array();
	var $runtime;

  // constructor
	function __construct() { 
   $this->start('total');
   $this->runtime = date('d/m/Y H:i');
   $this->counters['Note'] = 'Les valeurs sont en millisecondes';
   $this->used = 0;
   $this->php_version = phpversion(); 

   $ini_values = ini_get_all();
   $this->php_max_execution_time = $ini_values['max_execution_time']['local_value'];
   $this->php_memory_limit = (int) $ini_values['memory_limit']['local_value'];

  } // END constructor
  
  function show() {
    $this->stop('total');
    $this->memory_usage = $this->human_bytes(memory_get_usage());
    echo '<pre>'.print_r($this,1).'</pre>';
  }
  
  function human_bytes($bytes) {
    $s = array('o', 'Ko', 'Mo', 'Go', 'To', 'Po');
    $e = floor(log($bytes)/log(1024));
    return sprintf('%.2f '.$s[$e], ($bytes/pow(1024, floor($e))));
  }

  function show_memory_usage() {
    echo 'Memory Usage : '.round(memory_get_usage()/1048576,2).' M / '.$this->php_memory_limit.' M';
  }
  
  function reaching_time_limit() {
   if(round(memory_get_usage()/1048576,2) > $this->php_memory_limit) return true;
   if($this->time_spent_since('total') > ($this->php_max_execution_time * 0.9)) return true;
   return false;
 }


 function getmicrotime() { 
  list($usec, $sec) = explode(" ",microtime()); 
  return ((float)$usec + (float)$sec);
}

function add_item($arrname,$value) {
 if(!$this->used) $this->used = 1;
 if(!isset($this->markers[$arrname]) || is_array($this->markers[$arrname])) unset($this->markers[$arrname]); 

 $this->markers[$arrname][] = $value;
}

function add($marker,$time) {
 if(!$this->used) $this->used = 1;
 if(!isset($this->markers[$marker]['time'])) $this->markers[$marker]['time'] = $time;
 elseif($time > 0) $this->markers[$marker]['time'] += $time;

}

function start($marker) {
    //echo "<br />on start $marker";
  if(empty($this->used) || !$this->used) $this->used = 1;
  $this->holders[$marker] = $this->getmicrotime();
  if(!isset($this->counters[$marker])) $this->counters[$marker] = 0;
}

function time_spent_since($marker) {
  return $this->counters[$marker] + round( 1000 * ($this->getmicrotime() - $this->holders[$marker]),3);
}    

function stop($marker) {
    //echo "<br />on stop $marker";
  $this->counters[$marker] = $this->counters[$marker] + round( 1000 * ($this->getmicrotime() - $this->holders[$marker]),3);
  unset($this->holders[$marker]);
}

function get($marker) {
  $this->counters[$marker] = $this->counters[$marker] + round( 1000 * ($this->getmicrotime() - $this->holders[$marker]),3);
  $this->holders[$marker] = $this->getmicrotime();
  return $this->counters[$marker];
}

function increment($marker) {
 if(!$this->used) $this->used = 1;
 if(!isset($this->counters[$marker])) $this->counters[$marker] = 1;
 else $this->counters[$marker]++;

}

function average() {
  unset($this->holders);
  foreach($this->markers as $v => $ks)
  {
    if(isset($ks['time']))
      if(isset($ks['n'])) $this->markers[$v]['average'] = round(1000 * $ks['time'] / $ks['n'],4). ' ms';
    else $this->markers[$v]['time'] = round(1000 * $this->markers[$v]['time'],4).' ms';
  }

}
} // END class counter
