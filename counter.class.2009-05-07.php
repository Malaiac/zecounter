<?php
/**
 * zeList Counter Class
 * Profiling purposes 
 *
 * @package zeList
 * @subpackage Plugin
 */
if(class_exists('counter') === false)
{
class counter
{ // BEGIN class counter
	// variables
	var $markers = array();
	var $timers = array();
	var $holders = array();
	var $runtime;
  
  // constructor
	function counter()
	{ // BEGIN constructor
	$this->start('total');
  $this->runtime = date('d/m/Y H:i');
	$this->used = 0;
	$this->php_version = phpversion(); 
    
  $ini_values = ini_get_all();
  $this->php_max_execution_time = $ini_values['max_execution_time']['local_value'];
  $this->php_memory_limit = (int) $ini_values['memory_limit']['local_value'];
  
  } // END constructor
  
  function show()
  {
  $this->stop('total');
  if($this->holders)
  foreach($this->holders as $name => $value)
    {
    $halted[] = $name;
    $this->stop($name);
    }
  
  $html = '
  <style>
  table#debug_counter {  background: #FAFAFA; font-family: Helvetica; font-size: 10px; text-align: left; }
  table#debug_counter th, table#debug_counter td { border: none; margin: 0; padding: .3em; line-height: 1em; }
  table#debug_counter tr.odd td { background: #EAEAEA; }';
  if(!is_admin()) $html .= '
  table#debug_counter { position: absolute; top: 20px; right: 20px; width: 200px; z-index: 100; } ';
  $html .= '
  </style>
  <table id="debug_counter" cellpadding="0" cellspacing="0">
  <tr><th scope="col">Counter</th><th scope="col" width="50px">Time (sec)</th></tr>
  ';
  $odd = 'odd';
  foreach($this->timers as $name => $time)
    {
    if($time < 10) continue;
    $odd = ($odd == 'odd') ? 'even' : 'odd';
    $html .= '
    <tr class="'.$odd.'"><td>'.$name.'</td><td>'.round($time/1000,4).'</td></tr>';
    }
  if($this->counters) 
		foreach($this->counters as $name => $count)
    {
    if($time < 10) continue;
    $odd = ($odd == 'odd') ? 'even' : 'odd';
    $html .= '
    <tr class="'.$odd.'"><td>'.$name.'</td><td>'.$count.'</td></tr>';
    }

  $html .= '
  </table>';
  echo $html;

  if($halted)
  foreach($halted as $name)
    {
    $this->start($name);
    }  
  $this->start('total');
  }
  
	function show_memory_usage()
		{
		if(function_exists('memory_get_usage')) 
		  echo 'Memory Usage : '.round(memory_get_usage()/1048576,2).' M / '.$this->php_memory_limit.' M';
	 }
	function reaching_time_limit()
		{
		if(function_exists('memory_get_usage') && round(memory_get_usage()/1048576,2) > $this->php_memory_limit) 
		  return true;
		if($this->time_spent_since('total') > ($this->php_max_execution_time * 0.9)) 
		  return true;
	 return false;
  }
     
   
	function getmicrotime()
  { 
  list($usec, $sec) = explode(" ",microtime()); 
   return ((float)$usec + (float)$sec);
  }

	function add_item($arrname,$value)
   {
   if(!$this->used) $this->used = 1;
   if(!isse($this->markers[$arrname]) || is_array($this->markers[$arrname])) unset($this->markers[$arrname]); 
   
   $this->markers[$arrname][] = $value;
   }
	function add($marker,$time)
	  {
	  if(!$this->used) $this->used = 1;
	  if(!isset($this->markers[$marker]['time'])) $this->markers[$marker]['time'] = $time;
    elseif($time > 0) $this->markers[$marker]['time'] += $time;
    
    }
  function start($marker)
    {
    //echo "<br />on start $marker";
    
    if(empty($this->used) || !$this->used) $this->used = 1;
    $this->holders[$marker] = $this->getmicrotime();
    if(!isset($this->timers[$marker])) $this->timers[$marker] = 0;
    }

  function time_spent_since($marker)
    {
    return $this->timers[$marker] + round( 1000 * ($this->getmicrotime() - $this->holders[$marker]),3);
    }    
  function stop($marker)
    {
    //echo "<br />on stop $marker";
    $this->timers[$marker] = $this->timers[$marker] + round( 1000 * ($this->getmicrotime() - $this->holders[$marker]),3);
    unset($this->holders[$marker]);
    }
  
  function get($marker)
    {
    $this->timers[$marker] = $this->timers[$marker] + round( 1000 * ($this->getmicrotime() - $this->holders[$marker]),3);
    $this->holders[$marker] = $this->getmicrotime();
    return $this->timers[$marker];
    }
    
  function increment($marker)
   {
   if(!$this->used) $this->used = 1;
   if(!isset($this->counters[$marker])) $this->counters[$marker] = 1;
   else $this->counters[$marker]++;
    
   }
   
   function average()
    {
    unset($this->holders);
    foreach($this->markers as $v => $ks)
      {
      if(isset($ks['time']))
        if(isset($ks['n'])) $this->markers[$v]['average'] = round(1000 * $ks['time'] / $ks['n'],4). ' ms';
        else $this->markers[$v]['time'] = round(1000 * $this->markers[$v]['time'],4).' ms';
      }
    
    
    
    }
} // END class counter
}
?>
