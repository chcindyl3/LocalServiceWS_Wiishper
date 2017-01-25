<?php

	require_once 'DBConnection.php';
	require_once 'utils/ApiException.php';
	require_once 'utils/WSConstants.php';
	require_once 'UsersModel.php';

	class FriendsModel
	{

		 const TABLE_NAME = "friends";
		 const FRIENDER = "friender";
		 const BEFRIEND_DATE = "befriend_date";
		 const FRIENDEE = "friendee";

		public static function listAll()
		{
			try
			{
				$command = "SELECT * FROM " . self::TABLE_NAME;

				$statement = DBConnection::getInstance()->obtainDB()->prepare($command);

				if($statement->execute())
				{
					$friends = array();
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);
					foreach( $result as $value )
					{
						array_push($friends, $value);
					}

					return $friends;
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

		public static function get($id, $friendee)
		{
			try
			{
				$command = "SELECT * FROM " . self::TABLE_NAME . " WHERE " . self::FRIENDEE."=? AND " . self::FRIENDER . " =?";
				$statement = DBConnection::getInstance()->obtainDB()->prepare($command);
				$statement->bindParam(2,$id);
				$statement->bindParam(1,$friendee);

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
				$friender = $data->friender;
				$friendee = $data->friendee;

				$command = "INSERT INTO " . self::TABLE_NAME . " ( " . self::FRIENDER . ", " . self::BEFRIEND_DATE . ", " . self::FRIENDEE . " ) VALUES (?, now(), ?)";
				$statement = DBConnection::getInstance()->obtainDB()->prepare($command);

				$statement->bindParam(1, $friender);
				$statement->bindParam(2, $friendee);

				$result = $statement->execute();

				return $result ? WSConstants::STATE_CREATE_SUCCESS : WSConstants::STATE_CREATE_FAILED;
			}
			catch(PDOException $e)
			{
				throw new APIException(WSConstants::STATE_DB_ERROR, $e->getMessage());
			}
		}
		
		public static function delete($id, $friendee)
		{
			try
			{
				$command = "DELETE FROM " . self::TABLE_NAME . " WHERE " . self::FRIENDEE. "=? AND " . self::FRIENDER . "=?";
				$statement = DBConnection::getInstance()->obtainDB()->prepare($command);

				$statement->bindParam(1,$friendee);
				$statement->bindParam(2, $id);
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
				$friender = $data->friender;
				$befriend_date = $data->befriend_date;
				$friendee = $data->friendee;
				$command = "UPDATE " . self::TABLE_NAME . " SET " . self::FRIENDER. " =?, " .self::BEFRIEND_DATE. " =?, " .self::FRIENDEE. " =?   WHERE " . self::FRIENDEE . "=?"; 
				$statement = DBConnection::getInstance()->obtainDB()->prepare($command);

				$statement->bindParam(1, $friender);
				$statement->bindParam(2, $befriend_date);
				$statement->bindParam(3, $friendee);

				$statement->bindParam(4, $id);
				$statement->execute();

				$rowCount = $statement->rowCount();

				 return $rowCount > 0 ? WSConstants::STATE_UPDATE_SUCCESS : WSConstants::STATE_UPDATE_FAILED;
			}
			catch(PDOException $e)
			{
				throw new APIException(WSConstants::STATE_DB_ERROR, $e->getMessage());
			}
		}
		
		public static function getFriends($userID, $friendID = NULL)
		{
			try
			{
				if(!$friendID)
				{
					$command = "SELECT * FROM " . self::TABLE_NAME . " WHERE " . self::FRIENDER . " =?";
					
					$statement = DBConnection::getInstance()->obtainDB()->prepare($command);
					$statement->bindParam(1, $userID, PDO::PARAM_INT);
					
				}
				else
				{
					$command = "SELECT * FROM " . self::TABLE_NAME . " WHERE " . self::FRIENDER . " =? AND " . self::FRIENDEE . " =?";
					
					$statement = DBConnection::getInstance()->obtainDB()->prepare($command);
					$statement->bindParam(1, $userID, PDO::PARAM_INT);
					$statement->bindParam(2, $friendID, PDO::PARAM_INT);
					
				}
				
				if($statement->execute())
				{
					$friends = array();
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);
					foreach( $result as $value)
					{
                                            $user = UsersModel::get($value[self::FRIENDEE]);
                                            $user['isfriend'] = true;
						array_push($friends, $user);
					}
					
					http_response_code(200);
					
					return $friends;
				}
				else
				{
					throw new ApiException(WSConstants::STATE_UNKNOW_FAILURE, "Se produjo un error desconocido");
				}
			}
			catch (PDOException $e)
			{
				throw new ApiException(WSConstants::STATE_DB_ERROR, $e->getMessage());
			}
		}
		
		public static function getNOFriends($userID)
		{
			try
			{
				$command = "SELECT DISTINCT(idusers) FROM users LEFT JOIN friends ON (idusers = friender) WHERE idusers <> ? AND idusers NOT IN (SELECT DISTINCT(friendee) FROM friends WHERE friender = ?); ";
						
				$statement = DBConnection::getInstance()->obtainDB()->prepare($command);
				$statement->bindParam(1, $userID, PDO::PARAM_INT);
				$statement->bindParam(2, $userID, PDO::PARAM_INT);
				
				if($statement->execute())
				{
					$friends = array();
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);
					foreach( $result as $value)
					{
                                            $user = UsersModel::get($value[UsersModel::IDUSERS]);
                                            $user['isfriend'] = false;
                                            
						array_push($friends, $user);
					}
					
					http_response_code(200);
					
					return $friends;
				}
				else
				{
					throw new ApiException(WSConstants::STATE_UNKNOW_FAILURE, "Se produjo un error desconocido");
				}
			}
			catch (PDOException $e)
			{
				throw new ApiException(WSConstants::STATE_DB_ERROR, $e->getMessage());
			}
		}
	}
?>
