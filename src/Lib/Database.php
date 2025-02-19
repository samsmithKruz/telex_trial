<?php

namespace App\Lib;

use PDO;
use PDOException;

/*
   * PDO Database Class
   * Connect to database
   * Create prepared statements
   * Bind values
   * Return rows and results
   */
//   require_once "config.php";
class Database
{
  private $host;
  private $user;
  private $pass;
  private $dbname;

  private $dbh;
  private $stmt;
  private $error;

  public function __construct()
  {
    $this->host = getenv("DB_HOST");
    $this->user = getenv("DB_USER");
    $this->pass = getenv("DB_PASS");
    $this->dbname = getenv("DB_NAME");
    // Set DSN
    $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->dbname;
    $options = array(
      PDO::ATTR_PERSISTENT => true,
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    );

    // Create PDO instance
    try {
      $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
    } catch (PDOException $e) {
      $this->error = $e->getMessage();
      echo $this->error;
    }
  }

  // Start Transaction
  public function beginTransaction($isolationLevel = "SERIALIZABLE")
  {
    // set isolation level 
    // SERIALIZABLE, READ COMMITTED, READ UNCOMMITTED, REPEATABLE READ
    $this->dbh->exec("SET TRANSACTION ISOLATION LEVEL $isolationLevel");

    // Start Transaction
    $this->dbh->beginTransaction();
  }
  // Commit Transaction
  public function commitTransaction()
  {
    $this->dbh->commit();
  }
  // Start Transaction
  public function rollbackTransaction()
  {
    $this->dbh->rollback();
  }

  // Prepare statement with query
  public function query($sql)
  {
    $this->stmt = $this->dbh->prepare($sql);
    return $this;
  }

  // Bind values
  public function bind($param, $value, $type = null)
  {
    if (is_null($type)) {
      switch (true) {
        case is_int($value):
          $type = PDO::PARAM_INT;
          break;
        case is_bool($value):
          $type = PDO::PARAM_BOOL;
          break;
        case is_null($value):
          $type = PDO::PARAM_NULL;
          break;
        default:
          $type = PDO::PARAM_STR;
      }
    }

    $this->stmt->bindValue($param, $value, $type);
    return $this;
  }

  // Execute the prepared statement
  public function execute()
  {
    try {
      return $this->stmt->execute();
    } catch (PDOException $e) {
      $this->error = $e->getMessage();
      echo $this->error;
      return false;
    }
  }

  // Get result set as array of objects
  public function resultSet()
  {
    $this->execute();
    return $this->stmt->fetchAll(PDO::FETCH_OBJ);
  }

  // Get single record as object
  public function single()
  {
    $this->execute();
    return $this->stmt->fetch(PDO::FETCH_OBJ);
  }

  // Get row count
  public function rowCount()
  {
    return $this->stmt->rowCount();
  }
  // Get last insert ID
  public function lastInsertId()
  {
    return $this->dbh->lastInsertId();
  }

  // Close Connection
  public function __destruct()
  {
    $this->dbh = null;
  }
}
