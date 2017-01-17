<?php
	
	require_once 'utils/ApiException.php';
	require_once 'utils/WSConstants.php';
	require_once 'models/ProductsModel.php';
	require_once 'models/TypesModel.php';
	require_once 'models/TastesModel.php';
	require_once 'AccessManager.php';
	require_once 'Processor.php';
	
	class ProductsManager implements Processor
	{
		
		public function process($operation, $data)
		{
                        if($operation >= WSConstants::OPER_SHOW_PRODS_PARAM && $operation <= WSConstants::OPER_REJ_PROD)
                        {
                            switch ($operation)
                            {
                                case WSConstants::OPER_SHOW_PRODS_PARAM:
                                    return $this->showProductsParam($data);
                                case WSConstants::OPER_ADD_PROD:
                                    return $this->alterProd($data->idproducts, $data->state);
                                case WSConstants::OPER_REJ_PROD:
                                    return $this->alterProd($data->idproducts, $data->state);
                            }
                        }
			$userID = AccessManager::authorize();
			if($userID != null)
			{
				switch($operation)
				{
					case WSConstants::OPER_SHOW_RND_PRODUCTS:
						return $this->showRandomProducts();
					case WSConstants::OPER_LIKE_PRODUCT:
						return $this->likeProduct($userID, $data);
					case WSConstants::OPER_REJECT_PRODUCT:
						return $this->likeProduct($userID, $data);
					case WSConstants::OPER_FILTER_PRODUCTS:
						return [WSConstants::FIELD_MESSAGE=>"Operation not implemented", WSConstants::FIELD_OPERATION=>WSConstants::OPER_FILTER_PRODUCTS];
					case WSConstants::OPER_SHOW_LIKED_PRODUCTS:
						return $this->showSeenProducts(isset($data->userid) ? $data->userid : $userID, true);
					case WSConstants::OPER_SHOW_REJECTED_PRODS:
						return $this->showSeenProducts(isset($data->userid) ? $data->userid : $userID, false);
					case WSConstants::OPER_SYNC_PRODUCTS:
						return [WSConstants::FIELD_MESSAGE=>"Operation not implemented", WSConstants::FIELD_OPERATION=>WSConstants::OPER_SYNC_PRODUCTS];
					default:
						throw new ApiException(300, utf8_encode("Operación no válida"));
				}
			}
			else
			{
				throw new ApiException(300, utf8_encode("Usuario no autorizado"));
			}
		}
		
		private function showRandomProducts()
		{
			$types = TypesModel::listAll();
			$products = array();
			
			if(count($types) > 0)
			{
				for($i = 0; $i < 6; $i++)
				{
					$typeid = $types[rand(0, count($types) - 1)][TypesModel::IDTYPE];
					$products = array_merge($products, ProductsModel::getByType($typeid, 3));
				}
				return [WSConstants::FIELD_STATE=>000, WSConstants::FIELD_DATA=>$products];
			}
			else
			{
				throw new ApiException(WSConstants::OPER_SHOW_RND_PRODUCTS, utf8_encode("No se encontraron tiendas..."));
			}
		}
		
		private function likeProduct($userID, $data)
		{
			if(!isset($data->idproducts) || !isset($data->liked))
			{
				throw new ApiException(WSConstants::OPER_LIKE_PRODUCT, utf8_encode("Formato de mensaje inválido"));
			}
			else
			{
				$taste = TastesModel::getByUser($data->idproducts, $userID);
				
				if($taste)
				{
					if($taste[TastesModel::LIKED] == $data->liked)
						return [WSConstants::FIELD_STATE=>000, WSConstants::FIELD_DATA => utf8_encode("Registro sin cambios")];
					else
					{
						$data->idusers = $userID;
						$result = TastesModel::update($data->idproducts, $data);
						
						switch($result)
						{
							case WSConstants::STATE_UPDATE_SUCCESS:
							http_response_code(200);
							return [WSConstants::FIELD_STATE=>000, WSConstants::FIELD_DATA => utf8_encode("¡Registro actualizado!")];
							break;
						case WSConstants::STATE_UPDATE_FAILED:
							throw new ApiException(WSConstants::STATE_UPDATE_FAILED, "Ocurrió un error");
							break;
						default:
							throw new ApiException(WSConstants::STATE_UNKNOW_FAILURE, "Falla desconocida");
						}
					}
				}
				else
				{
					$data->idusers = $userID;
					$result = TastesModel::create($data);
						
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
			}
		}
		
		private function showSeenProducts($userID, $liked = true)
		{
			$products = TastesModel::getByPreference($userID, $liked);
			
			if(empty($products))
			{
				return [WSConstants::FIELD_STATE=>000, WSConstants::FIELD_DATA=>utf8_encode("No ha evaluado ningún producto")];
			}
			else
			{
				$list = array();
				foreach($products as $value)
				{
					array_push($list, ProductsModel::get($value[TastesModel::IDPRODUCTS])); 
				}
				return [WSConstants::FIELD_STATE=>000, WSConstants::FIELD_DATA=>$list];
			}
		}
                
                private function showProductsParam($data)
		{
                	$types = TypesModel::listAll();
			$products = array();  
                        
			if(count($types) > 0)
			{
				for($i = 0; $i < 6; $i++)
				{
					$typeid = $types[rand(0, count($types) - 1)][TypesModel::IDTYPE];
                                        
					$products = array_merge($products, ProductsModel::getByTypeAndClass($typeid, $data->state, 3));
				}
				return [WSConstants::FIELD_STATE=>000, WSConstants::FIELD_DATA=>$products];
			}
			else
			{
				throw new ApiException(WSConstants::OPER_SHOW_RND_PRODUCTS, utf8_encode("No se encontraron tiendas..."));
			}
		}
                
                private function alterProd($idproducts, $newstate)
                {
                    $result = ProductsModel::updateState($idproducts, $newstate);
                    switch($result)
                    {
			case WSConstants::STATE_UPDATE_SUCCESS:
			    return [WSConstants::FIELD_STATE=>000, WSConstants::FIELD_DATA => utf8_encode("¡Actualización exitosa!")];
                       	
			case WSConstants::STATE_UPDATE_FAILED:
			    throw new ApiException(WSConstants::STATE_UPDATE_FAILED, utf8_encode("Ocurrió un error"));
			
			default:
			    throw new ApiException(WSConstants::STATE_UNKNOW_FAILURE, utf8_encode("Falla desconocida"));
			}
                }
		
	}
	
?>