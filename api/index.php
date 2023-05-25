<?php

use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;

require './vendor/autoload.php';

$container = new FactoryDefault();
$container->set(
    'mongo',
    function () {
        $mongo = new MongoDB\Client('mongodb+srv://myAtlasDBUser:myatlas-001@myatlas' .
            'clusteredu.aocinmp.mongodb.net/?retryWrites=true&w=majority');
        return $mongo->products->product;
    },
    true
);

$app = new Micro($container);

$app->get(
    '/api/product',
    function () {
        $product = $this->mongo->find();
        foreach ($product as $value) {
            $result[] = [
                'id'   =>  $value->id,
                'name' =>  $value->name,
                'price' => $value->price,
                'color' => $value->color,
            ];
        }
        echo json_encode($result);
    }
);

$app->get(
    '/api/product/search/{name}',
    function ($name) {
        $result = $this->mongo->findOne(['name' => $name]);
        if (empty($result)) {
            echo "data not matched";
        } else {
            echo json_encode($result);
        }
    }
);

$app->get(
    '/api/product/search/{id:[0-9]+}',
    function ($id) {
        $result = $this->mongo->findOne(['id' => $id]);
        if (empty($result)) {
            return  "data not matched";
        } else {
            return json_encode($result);
        }
    }
);
$app->post(
    '/api/product',
    function () {
        $data = (json_decode(file_get_contents('php://input')));
        $this->mongo->insertOne($data);
        echo "data added succesfully " . json_encode($data);
    }
);

$app->put(
    '/api/product/{id:[0-9]+}',
    function ($id) {
        $data = (json_decode(file_get_contents('php://input')));
        $this->mongo->updateOne(['id' => $id], ['$set' => $data]);
        echo "data updated succesfully " . json_encode($data);
    }
);
$app->delete(
    '/api/product/{id:[0-9]+}',
    function ($id) {
        $data = $this->mongo->findOne(['id' => $id]);
        $this->mongo->deleteOne(['id' => $id]);
        echo "data deleted succesfully " . json_encode($data);
    }
);

$app->notFound(
    function () use ($app) {
        $app->response->setStatusCode(404, 'Not Found');
        $app->response->sendHeaders();

        $message = 'Nothing to see here. Move along....';
        $app->response->setContent($message);
        $app->response->send();
    }
);

$app->handle(
    $_SERVER["REQUEST_URI"]
);
