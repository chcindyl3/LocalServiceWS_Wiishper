<?php
	
	require_once 'utils/ApiException.php';
	require_once 'utils/WSConstants.php';
	require_once 'models/UsersModel.php';
	require_once 'models/FriendsModel.php';
	require_once 'AccessManager.php';
	require_once 'Processor.php';
	
	class PeopleManager implements Processor
	{
		
		public function process($operation, $data)
		{
			$userID = AccessManager::authorize();
			if($userID != null)
			{
				switch($operation)
				{
					case WSConstants::OPER_SHOW_FRIENDS:
						return $this->listFriends($userID, $data);
					case WSConstants::OPER_SEARCH_PLP:
						return $this->listPeople($userID, $data);
					case WSConstants::OPER_ADD_FRIEND:
						return $this->addFriend($userID, $data);
					case WSConstants::OPER_REM_FRIEND:
						return $this->unfriend($userID, $data);
					case WSConstants::OPER_SYNC_FRIENDS:
						return [WSConstants::FIELD_DATA=>"Operation not implemented", WSConstants::FIELD_STATE=>WSConstants::OPER_SYNC_FRIENDS];
					case WSConstants::OPER_IS_FRIEND:
						return $this->isFriend($userID, $data);
					default:
						throw new ApiException(200, utf8_encode("Operación no válida"));
				}
			}
			else
			{
				throw new ApiException(200, utf8_encode("Usuario no autorizado"));
			}			
		}
		
		private function listFriends($userID, $data)
		{
			$friends = FriendsModel::getFriends($userID, isset($data->friendee) ? $data->friendee : null);
			
			if(count($friends) > 0)
			{
				return [WSConstants::FIELD_STATE=>000, WSConstants::FIELD_DATA=>$friends];
			}
			else
			{
				throw new ApiException(WSConstants::OPER_SHOW_FRIENDS, utf8_encode("Oooops! No friends found"));
			}
		}
		
		private function listPeople($userID, $data)
		{
			$users = FriendsModel::getNOFriends($userID);
			
			if(count($users > 0))
			{
				return [WSConstants::FIELD_STATE=>000, WSConstants::FIELD_DATA=>$users];
			}
			else
			{
				throw new ApiException(WSConstants::OPER_SEARCH_PLP, utf8_encode("Oooops! No people found :("));
			}
		}
		
		private function addFriend($userID, $data)
		{
			if(isset($data->friendee))
			{
				$data->friender = $userID;
				$result = FriendsModel::create($data);
				
				switch($result)
				{
					case WSConstants::STATE_CREATE_SUCCESS:
						http_response_code(200);
						return [WSConstants::FIELD_STATE=>000, WSConstants::FIELD_DATA => utf8_encode("¡Registro exitoso!")];
						break;
					case WSConstants::STATE_CREATE_FAILED:
						throw new ApiException(WSConstants::STATE_CREATE_FAILED, "Ocurrió un error");
						break;
					default:
						throw new ApiException(WSConstants::STATE_UNKNOW_FAILURE, "Falla desconocida");
				}
			}
			else
			{
				throw new ApiException(WSConstants::OPER_ADD_FRIEND, "Friend id cannot be null");
			}
		}
		
		private function unfriend($userID, $data)
		{
			if(isset($data->friendee))
			{
				$result = FriendsModel::delete($userID, $data->friendee);
				
				switch($result)
				{
					case WSConstants::STATE_DELETE_SUCCESS:
						http_response_code(200);
						return [WSConstants::FIELD_STATE=>000, WSConstants::FIELD_DATA => utf8_encode("Unfriend succesful")];
						break;
					case WSConstants::STATE_CREATE_FAILED:
						throw new ApiException(WSConstants::STATE_DELETE_FAILED, "Ocurrió un error");
						break;
					default:
						throw new ApiException(WSConstants::STATE_UNKNOW_FAILURE, "Falla desconocida");
				}
			}
			else
			{
				throw new ApiException(WSConstants::OPER_ADD_FRIEND, "Friend id cannot be null");
			}
		}
		
		private function isFriend($userID, $data)
		{
			if(isset($data->friendee))
			{
				$result = FriendsModel::get($userID, $data->friendee);
				
				return [WSConstants::FIELD_STATE=>000, WSConstants::FIELD_DATA=> ($result != null) ? true : false];
			}
			else
			{
				throw new ApiException(WSConstants::OPER_ADD_FRIEND, "Friend id cannot be null");
			}
		}
		
	}
	
?>