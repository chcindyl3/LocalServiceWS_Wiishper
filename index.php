<?php



    require_once 'views/JSONView.php';

    require_once 'utils/ApiException.php';
	
	require_once 'utils/WSConstants.php';
	
	require_once 'controllers/CommonProcessor.php';

    

	$request = explode("/", $_GET['PATH_INFO']);



	$view = new JSONView();
	
	$processor = new CommonProcessor();

	

	set_exception_handler(function ($exception) use ($view) {

		$body = array(

			WSConstants::FIELD_STATE => $exception->state,

			WSConstants::FIELD_DATA => $exception->getMessage()

			);

			

		if($exception->getCode())

		{

			$view->state = $exception->getCode();

		}

		else

		{

			$view->state = 500;

		}

			

		$view->_print([WSConstants::FIELD_RESPONSE=>$body]);

	} 

	);

 

	$resource = array_shift($request);

	$existing_resources = array('process');

	

	if(!in_array($resource, $existing_resources))

	{

        throw new ApiException(000, utf8_encode("No existe el recurso solicitado"));

	}

	

	$method = strtolower($_SERVER['REQUEST_METHOD']);

	

	if($method != 'post')

	{

		throw new ApiException(000, utf8_encode("Método no soportado"));

	}

	else

	{
		$body = file_get_contents('php://input');
		$payload = json_decode($body);
		$data = $payload->data;
		$operation = $payload->control->operation;
		$view->_print($processor->process($operation, $data));
	}

	

	

?>