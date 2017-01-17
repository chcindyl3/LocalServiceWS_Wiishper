<?php


	require_once 'DBConnection.php';
	require_once 'utils/ApiException.php';
	require_once 'utils/WSConstants.php';


	class TastesModel
	{

		 const TABLE_NAME = "tastes";
		 const INTER_DATE = "inter_date";
		 const LIKED = "liked";
		 const IDUSERS = "idusers";
		 const IDPRODUCTS = "idproducts";

		public static function listAll()
		{
			try
			{
				$command = "SELECT * FROM " . self::TABLE_NAME;

				$statement = DBConnection::getInstance()->obtainDB()->prepare($command);

				if($statement->execute())
				{
					$tastes = array();
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);
					foreach( $result as $value )
					{
						array_push($tastes, $value);
					}

					return $tastes;
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
		
		public static function getByUser($idproduct, $userid)
		{
			try
			{
				$command = "SELECT * FROM " . self::TABLE_NAME . " WHERE " . self::IDPRODUCTS."=? AND " . self::IDUSERS . " =?";
				$statement = DBConnection::getInstance()->obtainDB()->prepare($command);
				$statement->bindParam(1,$idproduct);
				$statement->bindParam(2, $userid);

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
		
		public static function getByPreference($iduser, $liked)
		{
			try
			{
				$command = "SELECT * FROM " . self::TABLE_NAME . " WHERE " . self::LIKED."=? AND " . self::IDUSERS . " =?";
				$statement = DBConnection::getInstance()->obtainDB()->prepare($command);
				$statement->bindParam(1,$liked);
				$statement->bindParam(2, $iduser);

				if($statement->execute())
				{
					$tastes = array();
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);
					foreach( $result as $value )
					{
						array_push($tastes, $value);
					}

					return $tastes;
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

		public static function create($data)
		{

			try
			{
				$liked = $data->liked;
				$idusers = $data->idusers;
				$idproducts = $data->idproducts;

				$command = "INSERT INTO " . self::TABLE_NAME . " ( " . self::INTER_DATE . ", " . self::LIKED . ", " . self::IDUSERS . ", " . self::IDPRODUCTS . " ) VALUES (now(), ?, ?, ?)";
				$statement = DBConnection::getInstance()->obtainDB()->prepare($command);

				$statement->bindParam(1, $liked);
				$statement->bindParam(2, $idusers);
				$statement->bindParam(3, $idproducts);

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
				$liked = $data->liked;
				$idusers = $data->idusers;
				$idproducts = $data->idproducts;
				
				$command = "UPDATE " . self::TABLE_NAME . " SET " . self::INTER_DATE. " =now(), " .self::LIKED. " =?  WHERE " . self::IDPRODUCTS . "=? AND " . self::IDUSERS . "=?"; 
				$statement = DBConnection::getInstance()->obtainDB()->prepare($command);

				$statement->bindParam(1, $liked);
				$statement->bindParam(3, $idusers);
				$statement->bindParam(2, $idproducts);

				$statement->execute();

				$rowCount = $statement->rowCount();

				 return $rowCount > 0 ? WSConstants::STATE_UPDATE_SUCCESS : WSConstants::STATE_UPDATE_FAILED;
			}
			catch(PDOException $e)
			{
				throw new APIException(WSConstants::STATE_DB_ERROR, $e->getMessage());
			}
		}
	}
?>
