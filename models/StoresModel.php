<?php


	require_once 'DBConnection.php';
	require_once 'utils/ApiException.php';
	require_once 'utils/WSConstants.php';


	class StoresModel
	{

		 const TABLE_NAME = "stores";
		 const IDSTORES = "idstores";
		 const NAME = "name";

		public static function listAll()
		{
			try
			{
				$command = "SELECT * FROM " . self::TABLE_NAME;

				$statement = DBConnection::getInstance()->obtainDB()->prepare($command);

				if($statement->execute())
				{
					$stores = array();
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);
					foreach( $result as $value )
					{
						array_push($stores, $value);
					}

					return $stores;
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
				$command = "SELECT * FROM " . self::TABLE_NAME . " WHERE " . self::IDSTORES."=?";
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

		public static function create($data)
		{

			try
			{
				$idstores = $data->idstores;
				$name = $data->name;

				$command = "INSERT INTO " . self::TABLE_NAME . " ( " . self::IDSTORES . ", " . self::NAME . " ) VALUES (?, ?)";
				$statement = DBConnection::getInstance()->obtainDB()->prepare($command);

				$statement->bindParam(1, $idstores);
				$statement->bindParam(2, $name);

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
				$command = "DELETE FROM " . self::TABLE_NAME . " WHERE " . self::IDSTORES. "=?";
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
				$idstores = $data->idstores;
				$name = $data->name;
				$command = "UPDATE " . self::TABLE_NAME . " SET " . self::IDSTORES. " =?, " .self::NAME. " =?   WHERE " . self::IDSTORES . "=?"; 
				$statement = DBConnection::getInstance()->obtainDB()->prepare($command);

				$statement->bindParam(1, $idstores);
				$statement->bindParam(2, $name);

				$statement->bindParam(3, $id);
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
