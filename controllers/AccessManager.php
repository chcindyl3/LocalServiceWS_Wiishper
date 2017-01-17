<?php
	
	require_once 'utils/ApiException.php';
	require_once 'utils/WSConstants.php';
	require_once 'models/UsersModel.php';
	require_once 'Processor.php';
	
	class AccessManager implements Processor
	{
		
		public function process($operation, $data)
		{
			switch($operation)
			{
				case WSConstants::OPER_SIGNUP:
					return $this->signup($data);
				case WSConstants::OPER_LOGIN:
					return $this->login($data);
				case WSConstants::OPER_REFRESH_SESSION:
					return $this->login($data);
                                case WSConstants::OPER_UPDATE_USER:
                                        return $this->update($data);
				default:
					throw new ApiException(100, utf8_encode("Operación no válida"));
			}
		}
		
		private function signup($data)
		{
			$userdata = json_decode($data);
			$result = UsersModel::create($userdata);
			
			switch($result)
			{
				case WSConstants::STATE_CREATE_SUCCESS:
					http_response_code(200);
					return 
						[WSConstants::FIELD_STATE=>000, WSConstants::FIELD_DATA => utf8_encode("¡Registro exitoso!")];
					break;
				case WSConstants::STATE_CREATE_FAILED:
					throw new ApiException(WSConstants::STATE_CREATE_FAILED, "Ocurrió un error");
					break;
				default:
					throw new ApiException(WSConstants::STATE_UNKNOW_FAILURE, "Falla desconocida");
			}
		}
		
		private function login($data)
		{
			$response = array();
			$email = $data->email;
			$password = $data->password;
			
			if(UsersModel::authenticate($email, $password))
			{
				$user = UsersModel::obtainUserByEmail($email);
				
				if($user != null)
				{
					$response[UsersModel::APIKEY] = $user[UsersModel::APIKEY];
					return [WSConstants::FIELD_STATE=>000, WSConstants::FIELD_DATA=>$user];
				}
				else
				{
					throw new ApiException(WSConstants::OPER_LOGIN, "Error buscando el ID de usuario");
				}
			}
			else
			{
				throw new ApiException(WSConstants::OPER_LOGIN, "Correo o contraseña incorrectos");
			}
		}
                
                private function update($data)
                {
                    $userdata = json_decode($data);
			$result = UsersModel::update($userdata->idusers, $userdata);
			
			switch($result)
			{
				case WSConstants::STATE_UPDATE_SUCCESS:
					http_response_code(200);
					return 
						[WSConstants::FIELD_STATE=>000, WSConstants::FIELD_DATA => utf8_encode("¡Actualización exitosa!")];
					break;
				case WSConstants::STATE_UPDATE_FAILED:
					throw new ApiException(WSConstants::STATE_UPDATE_FAILED, "Ocurrió un error");
					break;
				default:
					throw new ApiException(WSConstants::STATE_UNKNOW_FAILURE, "Falla desconocida");
			}
                }
		
		public static function authorize()
		{
			$headers = apache_request_headers();
			
			if(isset($headers['Authorization']))
			{
				$apikey = explode(" ",$headers['Authorization'])[1];
				
				if(UsersModel::validateApikey($apikey))
				{
					return UsersModel::obtainUserID($apikey);
				}
				else
				{
					throw new ApiException(WSConstants::STATE_WRONG_APIKEY, "Clave de API no autorizada");
				}
			}
			else
			{
				throw new ApiException(WSConstants::STATE_NO_APIKEY, utf8_encode("Se requiere clave de API para autorización"));
			}			
		}
		
	}
	
?>