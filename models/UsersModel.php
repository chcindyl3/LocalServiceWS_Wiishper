<?php


	require_once 'DBConnection.php';
	require_once 'utils/ApiException.php';
	require_once 'utils/WSConstants.php';


	class UsersModel
	{

		 const TABLE_NAME = "users";
		 const PROFILEPIC = "profilepic";
		 const APIKEY = "apikey";
		 const SURNAME = "surname";
		 const PASSWORD = "password";
		 const IDUSERS = "idusers";
		 const USERNAME = "username";
		 const BIRTHDATE = "birthdate";
		 const EMAIL = "email";
		 const ENTRYDATE = "entrydate";
		 const NAME = "name";

		public static function listAll()
		{
			try
			{
				$command = "SELECT * FROM " . self::TABLE_NAME;

				$statement = DBConnection::getInstance()->obtainDB()->prepare($command);

				if($statement->execute())
				{
					$users = array();
					$result = $statement->fetchAll(PDO::FETCH_ASSOC);
					foreach( $result as $value )
					{
						array_push($users, $value);
					}

					return $users;
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
				$command = "SELECT * FROM " . self::TABLE_NAME . " WHERE " . self::IDUSERS."=?";
				$statement = DBConnection::getInstance()->obtainDB()->prepare($command);
				$statement->bindParam(1,$id);

				if ($statement->execute())
				{

					$response = $statement->fetch(PDO::FETCH_ASSOC);
				}
				else
				{
					throw new ApiException(WSConstants::STATE_NOT_FOUND, "No se encontraron registros");
				}
                                
                                $command = "SELECT COUNT(*) as count FROM friends INNER JOIN users ON friends.friendee = users.idusers WHERE users.idusers = ?";
                                $statement = DBConnection::getInstance()->obtainDB()->prepare($command);
                                $statement->bindParam(1, $id);
                                if($statement->execute())
                                {
                                    $result = $statement->fetch(PDO::FETCH_ASSOC);
                                    $response['followers'] = $result['count'];
                                }
                                else
                                {
                                    throw new ApiException(WSConstants::STATE_NOT_FOUND, "No se encontraron seguidores");
                                }
			}
			catch(PDOException $e)
			{
				throw new ApiException(WSConstants::STATE_DB_ERROR, $e->getMessage());
			}
		}

		public static function create($userdata)
		{
			$username = $userdata->username;
			$password = $userdata->password;
			$encryptedpassword = self::encryptPassword($password);
			$email = $userdata->email;
			$name = $userdata->name;
			$surname = $userdata->surname;
			$birthdate = $userdata->birthdate;			
			
			$apikey = self::generateApikey();
			
			try
			{
				$pdo = DBConnection::getInstance()->obtainDB();
				
				$command = "INSERT INTO " . self::TABLE_NAME . "( " .
					self::USERNAME . ", " .
					self::NAME . ", " .
					self::SURNAME . ", " .
					self::EMAIL . ", " .
					self::PASSWORD . ", " .
					self::BIRTHDATE . ", " .
					self::APIKEY . ", " .
					self::ENTRYDATE . ") " .
					" VALUES ( ?, ?, ?, ?, ?, ?, ?, now() )";
					
				$statement = $pdo->prepare($command);
				
				$statement->bindParam(1, $username);
				$statement->bindParam(2, $name);
				$statement->bindParam(3, $surname);
				$statement->bindParam(4, $email);
				$statement->bindParam(5, $encryptedpassword);
				$statement->bindParam(6, $birthdate);
				$statement->bindParam(7, $apikey);
				
				$result = $statement->execute();
				
				if($result)
				{
					return WSConstants::STATE_CREATE_SUCCESS;
				}
				else
				{
					return WSConstants::STATE_CREATE_FAILED;
				}
			}
			catch (PDOException $e)
			{
				throw new ApiException(WSConstants::STATE_DB_ERROR, $e->getMessage());
			}
		}
		
		public static function delete($id)
		{
			try
			{
				$command = "DELETE FROM " . self::TABLE_NAME . " WHERE " . self::IDUSERS. "=?";
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
				$profilepic = $data->profilepic;
				$apikey = $data->apikey;
				$surname = $data->surname;
				$password = $data->password;
                                $encryptedpassword = self::encryptPassword($password);
				$idusers = $data->idusers;
				$username = $data->username;
				$birthdate = $data->birthdate;
				$email = $data->email;
				$name = $data->name;
				$command = "UPDATE " . self::TABLE_NAME . " SET " . self::PROFILEPIC. " =?, " .self::APIKEY. " =?, " .self::SURNAME. " =?, " .self::PASSWORD. " =?, " .self::IDUSERS. " =?, " .self::USERNAME. " =?, " .self::BIRTHDATE. " =?, " .self::EMAIL. " =?, " .self::NAME. " =?   WHERE " . self::IDUSERS . "=?"; 
				$statement = DBConnection::getInstance()->obtainDB()->prepare($command);

				$statement->bindParam(1, $profilepic);
				$statement->bindParam(2, $apikey);
				$statement->bindParam(3, $surname);
				$statement->bindParam(4, $encryptedpassword);
				$statement->bindParam(5, $idusers);
				$statement->bindParam(6, $username);
				$statement->bindParam(7, $birthdate);
				$statement->bindParam(8, $email);
				$statement->bindParam(9, $name);

				$statement->bindParam(10, $id);
				$statement->execute();

				$rowCount = $statement->rowCount();

				 return $rowCount > 0 ? WSConstants::STATE_UPDATE_SUCCESS : WSConstants::STATE_UPDATE_FAILED;
			}
			catch(PDOException $e)
			{
				throw new APIException(WSConstants::STATE_DB_ERROR, $e->getMessage());
			}
		}
		
		public static function encryptPassword($plainPassword)
		{
			if($plainPassword)
				return hash('sha256', $plainPassword);
			else return null;
		}
		
		public static function generateApikey()
		{
			return md5(microtime().rand());
		}
		
		public static function authenticate($email, $password)
		{
			$command = "SELECT password FROM " . self::TABLE_NAME . " WHERE " . self::EMAIL . " =?";
			
			try
			{
				$statement = DBConnection::getInstance()->obtainDB()->prepare($command);
				$statement->bindParam(1, $email);
				$statement->execute();
				
				if($statement)
				{
					$result = $statement->fetch();
					return self::validatePassword($password, $result[self::PASSWORD]);
				}
				else
				{
					return false;
				}
			}
			catch (PDOException $e)
			{
				throw new ApiException(WSConstants::STATE_DB_ERROR, $e->getMessage());
			}
		}
		
		public static function validatePassword($plainPassword, $hashedPassword)
		{
			return hash('sha256', $plainPassword) == $hashedPassword;
		}
		
		public static function obtainUserByEmail($email)
		{
			$command = "SELECT * FROM " . self::TABLE_NAME .
					" WHERE " .self::EMAIL . "=?";
					
			$statement = DBConnection::getInstance()->obtainDB()->prepare($command);			
			$statement->bindParam(1, $email);			
			
			return $statement->execute() ? $statement->fetch(PDO::FETCH_ASSOC) : null;			
		}
		
		public static function validateApikey($apikey)
		{
			$command = " SELECT COUNT( " . self::IDUSERS . " ) FROM " . self::TABLE_NAME . " WHERE " . self::APIKEY . " =?";
			
			$statement = DBConnection::getInstance()->obtainDB()->prepare($command);
			$statement->bindParam(1, $apikey);
			
			$statement->execute();
			
			return $statement->fetchColumn(0) > 0;
		}
		
		public static function obtainUserID($apikey)
		{
			$command = "SELECT " . self::IDUSERS . " FROM " . self::TABLE_NAME . " WHERE " . self::APIKEY . " =?";
			
			$statement = DBConnection::getInstance()->obtainDB()->prepare($command);
			$statement->bindParam(1, $apikey);
			
			if($statement->execute())
			{
				$result = $statement->fetch(PDO::FETCH_ASSOC);
				return $result[self::IDUSERS];
			}
			else
				return null;
		}
		
	}
?>
