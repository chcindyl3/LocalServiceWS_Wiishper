<?php
	
	require_once 'utils/ApiException.php';
	require_once 'utils/WSConstants.php';
	require_once 'Processor.php';
	require_once 'AccessManager.php';
	require_once 'PeopleManager.php';
	require_once 'ProductsManager.php';
	
	class CommonProcessor implements Processor
	{
		private $access_manager;
		private $people_manager;
		private $products_manager;
		
		public function __construct()
		{
			$this->access_manager = new AccessManager();
			$this->people_manager = new PeopleManager();
			$this->products_manager = new ProductsManager();
		}
		
		public function process($operation, $data)
		{
                   
			if($operation == null)
				throw new ApiException(000, utf8_encode("Operación no puede ser null"));
			if($operation < 100)
				throw new ApiException(000, utf8_encode("Operación no válida"));
			
			switch(floor($operation / 100))
			{
				case 1:
					//Call access manager
					return $this->access_manager->process($operation, $data);
				case 2:
					//Call people manager
					return $this->people_manager->process($operation, $data);
				case 3:
					//Call product manager
					return $this->products_manager->process($operation, $data);
				default:
					throw new ApiException(000, utf8_encode("Operación no implementada"));
			}
		}
		
	}
?>