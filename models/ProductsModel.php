<?php


	require_once 'DBConnection.php';
	require_once 'utils/ApiException.php';
	require_once 'utils/WSConstants.php';


	class ProductsModel
	{

		 const TABLE_NAME = "products";
		 const IDTYPE = "idtype";
		 const IMAGE = "image";
		 const PRICE = "price";
		 const DESCRIPTION = "description";
		 const PUBLISHDATE = "publishdate";
		 const IDPRODUCTS = "idproducts";
		 const NAME = "name";
		 const IDSTORE = "idstore";
                 const SHOW = "`show`";

		public static function listAll()
		{
			try
			{
				$command = "SELECT * FROM " . self::TABLE_NAME;

				$statement = DBConnection::getInstance()->obtainDB()->prepare($command);

				if($statement->execute())
				{
					$products = array();
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);
					foreach( $result as $value )
					{
						array_push($products, $value);
					}

					return $products;
				}
				else
				{
					throw new ApiException(WSConstants::STATE_UNKNOW_FAILURE, "Se produjo un error desconocido");
				}
			}
			catch(PDOException $e)
			{
				throw new ApiException(WSConstants::STATE_DB_ERROR, $e->getMessage());
			}
		}

		public static function get($id)
		{
			try
			{
				$command = "SELECT * FROM " . self::TABLE_NAME . " WHERE " . self::IDPRODUCTS."=?";
				$statement = DBConnection::getInstance()->obtainDB()->prepare($command);
				$statement->bindParam(1,$id);

				if ($statement->execute())
				{

					return $statement->fetch(PDO::FETCH_ASSOC);
				}
				else
				{
					throw new ApiException(WSConstants::STATE_NOT_FOUND, "No se encontraron registros");
				}
			}
			catch(PDOException $e)
			{
				throw new ApiException(WSConstants::STATE_DB_ERROR, $e->getMessage());
			}
		}
		
		public static function getByStore($storeid, $quantity)
		{
			try
			{
				$command = "SELECT * FROM " . self::TABLE_NAME . " WHERE " .  self::IDSTORE . " =? ";

				$statement = DBConnection::getInstance()->obtainDB()->prepare($command);
				$statement->bindParam(1,$storeid);

				if($statement->execute())
				{
					$products = array();
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);
					foreach( $result as $value )
					{
						array_push($products, $value);
						$quantity--;
						if($quantity <= 0)
							break;
					}

					return $products;
				}
				else
				{
					throw new ApiException(WSConstants::STATE_UNKNOW_FAILURE, "Se produjo un error desconocido");
				}
			}
			catch(PDOException $e)
			{
				throw new ApiException(WSConstants::STATE_DB_ERROR, $e->getMessage());
			}
		}
		
		public static function getByType($typeid, $quantity)
		{
			try
			{
				$command = "SELECT products.name as name, products.price AS price, products.description AS desciption, stores.name AS store, products.idproducts AS idproducts, products.image AS image, products.publishdate AS publishdate
                                FROM products INNER JOIN stores ON stores.idstores = products.idstore INNER JOIN types ON products.idtype = types.idtype 
                                WHERE types.idtype = ?  AND products.idproducts NOT IN (SELECT tastes.idproducts FROM tastes WHERE tastes.idusers = ?);";

				$statement = DBConnection::getInstance()->obtainDB()->prepare($command);
				$statement->bindParam(1,$typeid);
                                $statement->bindParam(2, $userID);

				if($statement->execute())
				{
					$products = array();
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);
					foreach( $result as $value )
					{
						array_push($products, $value);
						$quantity--;
						if($quantity <= 0)
							break;
					}

					return $products;
				}
				else
				{
					throw new ApiException(WSConstants::STATE_UNKNOW_FAILURE, "Se produjo un error desconocido");
				}
			}
			catch(PDOException $e)
			{
				throw new ApiException(WSConstants::STATE_DB_ERROR, $e->getMessage());
			}
		}
                
                public static function getByTypeAndClass($typeid, $class, $quantity)
                {
                    try
                    {
                        $command = "SELECT products.name as name, products.price AS price, products.description AS desciption, stores.name AS store, products.idproducts AS idproducts, products.image AS image, products.publishdate AS publishdate
                                FROM products INNER JOIN stores ON stores.idstores = products.idstore INNER JOIN types ON products.idtype = types.idtype 
                                WHERE types.idtype = ? AND `show` = ?";
                        $statement = DBConnection::getInstance()->obtaindb()->prepare($command);
                        $statement->bindParam(1, $typeid);
                        $statement->bindParam(2, $class);
                        
                        if($statement->execute())
			{
                            $products = array();
                            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
			    foreach( $result as $value )
                            {
				array_push($products, $value);
				$quantity--;
				if($quantity <= 0)
                                  break;;
                            }
                            return $products;
			}
			else
			{
                            throw new ApiException(WSConstants::STATE_UNKNOW_FAILURE, "Se produjo un error desconocido");
			}
                        
                    } catch (PDOException $ex) {
                        throw new ApiException(WSConstants::STATE_DB_ERROR, $ex->getMessage());
                    }
                }
                public static function getByState($class, $quantity)
                {
                        $command = "SELECT products.name as name, products.price AS price, products.description AS desciption, stores.name AS store, products.idproducts AS idproducts, products.image AS image, products.publishdate AS publishdate
                                    FROM products INNER JOIN stores ON stores.idstores = products.idstore INNER JOIN types ON products.idtype = types.idtype 
                                    WHERE `show` = ?";
                       
                        $statement = DBConnection::getInstance()->obtaindb()->prepare($command);
                        $statement->bindParam(1, $class);
                        
                        if($statement->execute())
                        {
                            $products = array();
                            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                            $products = array();
                            
                            foreach ($result as $value)
                            {    
                                array_push($products, $value);
                                $quantity--;
                                if($quantity<=0)
                                    break;
                                
                                        
                            }
                            return $products;
                                    
                        }
                        else
                        {
                            throw new ApiException(WSConstants::STATE_UNKNOW_FAILURE, "Se produjo un error desconocido");
                        }
                       
                } 

                public static function create($data)
		{

			try
			{
				$idtype = $data->idtype;
				$image = $data->image;
				$price = $data->price;
				$description = $data->description;
				$idproducts = $data->idproducts;
				$name = $data->name;
				$idstore = $data->idstore;

				$command = "INSERT INTO " . self::TABLE_NAME . " ( " . self::IDTYPE . ", " . self::IMAGE . ", " . self::PRICE . ", " . self::DESCRIPTION . ", " . self::PUBLISHDATE . ", " . self::IDPRODUCTS . ", " . self::NAME . ", " . self::IDSTORE . " ) VALUES (?, ?, ?, ?, now(), ?, ?, ?)";
				$statement = DBConnection::getInstance()->obtainDB()->prepare($command);

				$statement->bindParam(1, $idtype);
				$statement->bindParam(2, $image);
				$statement->bindParam(3, $price);
				$statement->bindParam(4, $description);
				$statement->bindParam(5, $idproducts);
				$statement->bindParam(6, $name);
				$statement->bindParam(7, $idstore);

				$result = $statement->execute();

				return $result ? WSConstants::STATE_CREATE_SUCCESS : WSConstants::STATE_CREATE_FAILED;
			}
			catch(PDOException $e)
			{
				throw new APIException(WSConstants::STATE_DB_ERROR, $e->getMessage());
			}
		}
		public static function delete($id)
		{
			try
			{
				$command = "DELETE FROM " . self::TABLE_NAME . " WHERE " . self::IDPRODUCTS. "=?";
				$statement = DBConnection::getInstance()->obtainDB()->prepare($command);

				$statement->bindParam(1,$id);
				$statement->execute();

				$rowCount = $statement->rowCount();

				 return $rowCount > 0 ? WSConstants::STATE_DELETE_SUCCESS : WSConstants::STATE_DELETE_FAILED;
			}
			catch(PDOException $e)
			{
				throw new APIException(WSConstants::STATE_DB_ERROR, $e->getMessage());
			}
		}
		public static function update($id, $data)
		{
			try
			{
				$idtype = $data->idtype;
				$image = $data->image;
				$price = $data->price;
				$description = $data->description;
				$publishdate = $data->publishdate;
				$idproducts = $data->idproducts;
				$name = $data->name;
				$idstore = $data->idstore;
				$command = "UPDATE " . self::TABLE_NAME . " SET " . self::IDTYPE. " =?, " .self::IMAGE. " =?, " .self::PRICE. " =?, " .self::DESCRIPTION. " =?, " .self::PUBLISHDATE. " =?, " .self::IDPRODUCTS. " =?, " .self::NAME. " =?, " .self::IDSTORE. " =?   WHERE " . self::IDPRODUCTS . "=?"; 
				$statement = DBConnection::getInstance()->obtainDB()->prepare($command);

				$statement->bindParam(1, $idtype);
				$statement->bindParam(2, $image);
				$statement->bindParam(3, $price);
				$statement->bindParam(4, $description);
				$statement->bindParam(5, $publishdate);
				$statement->bindParam(6, $idproducts);
				$statement->bindParam(7, $name);
				$statement->bindParam(8, $idstore);

				$statement->bindParam(9, $id);
				$statement->execute();

				$rowCount = $statement->rowCount();

				 return $rowCount > 0 ? WSConstants::STATE_UPDATE_SUCCESS : WSConstants::STATE_UPDATE_FAILED;
			}
			catch(PDOException $e)
			{
				throw new APIException(WSConstants::STATE_DB_ERROR, $e->getMessage());
			}
		}
                
                public static function updateState($id, $newstate)
                {
                    try
                    {
                        $command = "UPDATE " .self::TABLE_NAME. " SET " .self::SHOW. " = ? WHERE " .self::IDPRODUCTS. " = ?";
                        
                        $statement = DBConnection::getInstance()->obtainDB()->prepare($command);
                        $statement->bindParam(1, $newstate);
                        $statement->bindParam(2, $id);
                        
                        $statement->execute();
                        
                        $rowCount = $statement->rowCount();

			return $rowCount > 0 ? WSConstants::STATE_UPDATE_SUCCESS : WSConstants::STATE_UPDATE_FAILED;
                    } 
                    catch (PDOException $ex) {
                        throw new ApiException(WSConstants::STATE_DB_ERROR, $ex->getMessage());
                    }
                }
	}
?>
