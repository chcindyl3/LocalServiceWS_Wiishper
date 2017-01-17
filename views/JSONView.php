<?php
	
	require_once 'ApiView.php';
	
	
	class JSONView extends ApiView
	{
		
		public function __construct($state = 200)
		{
			$this->state = $state;
		}
		
		public function _print($body)
		{
			if($this->state)
			{
				http_response_code($this->state);
			}
			header('Content-Type: application/json; charset=utf8');
			echo json_encode($body, JSON_PRETTY_PRINT);
			exit;
		}
	}