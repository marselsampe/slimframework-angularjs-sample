<?php

require 'vendor/autoload.php';

use \Slim\Slim;

$app = new Slim();

$app->get('/', function() use($app) {
    $app->response->setStatus(200);
    echo "Welcome to Toko API";
}); 

$app->get('/barang','getBarang');
$app->get('/barang/:idBarang','getBarangDetail');
$app->get('/barang/search/(:keyword)','getBarangSearch');
$app->post('/barang','insertBarang');
$app->delete('/barang/:idBarang','deleteBarang');
$app->put('/barang/:idBarang', 'updateBarang');

$app->run();

// Connect Database
function getDB() {
    $dbhost="localhost";
    $dbuser="root";
    $dbpass="root";
    $dbname="slimframework";
    
    $dbConnection = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass); 
    $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbConnection;
}

// GET api/barang
function getBarang() {
    $app = Slim::getInstance();
    try {
        $db = getDB();
        $sql = "SELECT * FROM barang ORDER BY namaBarang ASC";
        $stmt = $db->query($sql); 
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);

        $app->response->setStatus(200);
        $app->response->headers->set('Content-Type', 'application/json');
        echo json_encode($data);
        $db = null;
    } catch(PDOException $e) {
        $app->response()->setStatus(404);
        echo '{"error":{"text":"'. $e->getMessage() .'"}}';
    }
}

// GET api/barang/1
function getBarangDetail($idBarang) {
    $app = Slim::getInstance();
    try {
        $db = getDB();
        $sql = "SELECT * FROM barang WHERE idBarang=?";
        $stmt = $db->prepare($sql); 
        $stmt->execute(array($idBarang));
        $data = $stmt->fetch(PDO::FETCH_OBJ);

        if($data) {
            $app->response->setStatus(200);
            $app->response->headers->set('Content-Type', 'application/json');
            echo json_encode($data);
            $db = null;
        }else{
            throw new PDOException('No records found.');
        }
    } catch(PDOException $e) {
        $app->response()->setStatus(404);
        echo '{"error":{"text":"'. $e->getMessage() .'"}}';
    }
}

// GET api/barang/search/NAMA
function getBarangSearch($keyword='') {
    $app = Slim::getInstance();
    try {
        $db = getDB();
        $sql = "SELECT * FROM barang WHERE namaBarang LIKE ? ORDER BY namaBarang ASC";
        $stmt = $db->prepare($sql);
        $keyword='%'.$keyword.'%';
        $stmt->execute(array($keyword));
        $data = $stmt->fetchAll(PDO::FETCH_OBJ);

        $app->response->setStatus(200);
        $app->response->headers->set('Content-Type', 'application/json');
        echo json_encode($data);
        $db = null;
    } catch(PDOException $e) {
        $app->response()->setStatus(404);
        echo '{"error":{"text":"'. $e->getMessage() .'"}}';
    }
}

// POST api/barang
function insertBarang() {
    $app = Slim::getInstance();
    $post = json_decode($app->request->getBody());
    try {
        $db = getDB();
        $sql = "INSERT INTO barang (namaBarang, kategori, stok, hargaBeli, hargaJual) VALUES (?,?,?,?,?)";
        $stmt = $db->prepare($sql); 
        $stmt->execute(array($post->namaBarang, $post->kategori, $post->stok, $post->hargaBeli, $post->hargaJual));

        $app->response->setStatus(201);
        $app->response->headers->set('Content-Type', 'application/json');
        echo json_encode(array("status" => "success", "code" => 1));
        $db = null;
    } catch(PDOException $e) {
        $app->response()->setStatus(404);
        echo '{"error":{"text":"'. $e->getMessage() .'"}}';
    }
}

// PUT api/barang/1
function updateBarang($idBarang) {
    $app = Slim::getInstance();
    try {
        $db = getDB();
        $sql = "SELECT * FROM barang WHERE idBarang=?";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($idBarang));
        if($stmt->fetch()){
            $post = json_decode($app->request->getBody());

            $sql = "UPDATE barang SET namaBarang=?, kategori=?, stok=?, hargaBeli=?, hargaJual=? WHERE idBarang=?";
            $stmt = $db->prepare($sql); 
            $stmt->execute(array($post->namaBarang, $post->kategori, $post->stok, $post->hargaBeli, $post->hargaJual, $idBarang));

            $app->response->setStatus(200);
            $app->response->headers->set('Content-Type', 'application/json');
            echo json_encode(array("status" => "success", "code" => 1));
            $db = null;
        }else{
            throw new PDOException('No records found.');
        }
    } catch(PDOException $e) {
        $app->response()->setStatus(404);
        echo '{"error":{"text":"'. $e->getMessage() .'"}}';
    }
}

// DELETE api/barang/1
function deleteBarang($idBarang) {
    $app = Slim::getInstance();
    try {
        $db = getDB();
        $sql = "SELECT * FROM barang WHERE idBarang=?";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($idBarang));
        if($stmt->fetch()){
            $sql = "DELETE FROM barang WHERE idBarang=?";
            $stmt = $db->prepare($sql); 
            $stmt->execute(array($idBarang));

            $app->response->setStatus(200);
            echo json_encode(array("status" => "success", "code" => 1));
            $db = null;
        }else{
            throw new PDOException('No records found.');
        }
    } catch(PDOException $e) {
        $app->response()->setStatus(404);
        echo '{"error":{"text":"'. $e->getMessage() .'"}}';
    }
}