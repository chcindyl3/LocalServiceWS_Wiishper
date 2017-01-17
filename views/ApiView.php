<?php

	abstract class ApiView
	{
		public $state;
		
		public abstract function _print($body);
	}
    
?>