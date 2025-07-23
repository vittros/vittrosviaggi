<?php
// Funzioni di utility per accesso al DB, basate su getPDO()

function db_select_row($query, $params = []) {
  $db = getPDO();
  $stmt = $db->prepare($query);
  $stmt->execute($params);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

function db_select_all($query, $params = []) {
  $db = getPDO();
  $stmt = $db->prepare($query);
  $stmt->execute($params);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function db_insert($query, $params = []) {
  $db = getPDO();
  $stmt = $db->prepare($query);
  $stmt->execute($params);
  return $db->lastInsertId();
}

function db_update($query, $params = []) {
  $db = getPDO();
  $stmt = $db->prepare($query);
  return $stmt->execute($params);
}

function db_delete($query, $params = []) {
  $db = getPDO();
  $stmt = $db->prepare($query);
  return $stmt->execute($params);
}
