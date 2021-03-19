<?php
// require '../config/config.php';

class Database {
  private $host;
  private $username;
  private $password;
  private $database;
  private $port;
  private $mysqli;
  private $stmt;

  public function __construct($host, $username, $password, $database, $port) {
    $this->host = $host;
    $this->username = $username;
    $this->password = $password;
    $this->database = $database;
    $this->port = $port;

    $this->mysqli = mysqli_init();
    $this->mysqli->real_connect($this->host, $this->username, $this->password, $this->database, $this->port, null, MYSQLI_CLIENT_FOUND_ROWS);
    if ($this->mysqli->connect_errno) {
      echo "Failed to connect to MySQL: " . $this->mysqli->connect_error;
      die;
    }
  }

  // prepare query
  public function query($query) {
    $this->stmt = $this->mysqli->stmt_init();
    $this->stmt->prepare($query);
  }

  // binding data
  public function bind($types, $values) {
    $this->stmt->bind_param($types, ...$values);
  }

  public function execute() {
    if (!$this->stmt->execute()) {
      echo 'query error';
      die;
    }
  }

  public function resultSet() {
    $this->execute();
    $result = $this->stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
  }

  public function single() {
    $this->execute();
    $result = $this->stmt->get_result();
    return $result->fetch_array(MYSQLI_ASSOC);
  }

  public function rowCount() {
    return $this->stmt->affected_rows;
  }

  public function getResult() {
    $result = $this->stmt->get_result();
    return $result;
  }
}
