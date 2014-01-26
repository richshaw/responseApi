<?php
require '../bootstrap.php';

$config = require '../config/global.php';
$db = new \Response\Database($config['Mongo']['uri']);
$app = new \Slim\Slim($config['Slim']);
$app->add(new \Middleware\Auth());
$app->view(new \JsonApiView());
$app->add(new \JsonApiMiddleware());

$app->get('/', function () {
    $app->render(200,array('message' => "Timed response experiment **ONLINE**"));
});


/****
* Experiment routes
*/

$app->post('/experiment', function () use ($app,$db) {

    $request = $app->request();
    $params = $request->params();

    $experiment = new \Response\Experiment();

    $valid = $experiment->validate($params);

    if ($valid === true) {
        $experiment->setTitle($params['title']);
        $experiment->setBody($params['body']);
        $experiment->setInput($params['input']);
        $experiment->setRandom($params['random']);

        $experiment->save($db);

        $data = $experiment->getId();

        $app->render(200,array('data' => $data));
    }
    else {
        $errors = $valid->errors();
        $app->render(400,array('error' => true,'errors' => $errors));
    }
});


$app->get('/experiment',  function () use ($app,$db) {

    $experiments = new \Response\Experiments();

    $e = $experiments->load($db);

    $data = array();
    foreach ($e as $experiment) {
        $data[] = array(
            'id' => $experiment->getId(),
            'title' => $experiment->getTitle(),
            'body' => $experiment->getBody(),
            'input' => $experiment->getInput(),
            'random' => $experiment->getRandom()
        );
    }

    $app->render(200,array('data' => $data));
});

$app->get('/experiment/:expId',  function ($expId) use ($app,$db) {

    $experiment = new \Response\Experiment();

    $experiment->load($expId,$db);

    $data = array(
        'id' => $experiment->getId(),
        'title' => $experiment->getTitle(),
        'body' => $experiment->getBody(),
        'input' => $experiment->getInput(),
        'random' => $experiment->getRandom()
    );

    $app->render(200,array('data' => $data));
});


$app->put('/experiment/:expId',  function ($expId) use ($app,$db) {

    $request = $app->request();
    $params = $request->params();

    $experiment = new \Response\Experiment();

    $valid = $experiment->validate($params);

    if ($valid === true) {
        $experiment->setId($expId);

        $experiment->setTitle($params['title']);
        $experiment->setBody($params['body']);
        $experiment->setInput($params['input']);
        $experiment->setRandom($params['random']);

        $experiment->save($db);

        $data = $experiment->getId();
        $app->render(200,array('data' => $data));
    }
    else {
        $errors = $valid->errors();
        $app->render(400,array('error' => true,'errors' => $errors));
    }
});


$app->delete('/experiment/:expId',  function ($expId) use ($app,$db) {

    $experiment = new \Response\Experiment();

    $data = $experiment->delete($expId,$db);

    $app->render(200,array('data' => $data));

});

/****
* Response routes
*/

$app->post('/experiment/:expId/response', function ($expId) use ($app,$db) {

    $request = $app->request();
    $params = $request->params();

    $response = new \Response\Response();

    $valid = $response->validate($params);

    if ($valid === true) {
        $response->setInput($params['input']);
        $response->setExpId(new MoongoId($expId));
        $response->setParticipantId($params['participantId']);
        $response->setInput($params['input']);
        $response->setSlide($params['slide']);
        $response->setTime($params['time']);

        $response->save($db);

        $data = $response->getId();
        $app->render(200,array('data' => $data));
    }
    else {
        $errors = $valid->errors();
        $app->render(400,array('error' => true,'errors' => $errors));
    }

});


$app->get('/experiment/:expId/response',  function ($expId) use ($app,$db) {

    $responses = new \Response\Response();

    $r = $responses->load($db);

    $data = array();
    foreach ($r as $response) {
        $data[] = array(
            'id' => $response->getId(),
            'input' => $response->getInput(),
        );
    }

    $app->render(200,array('data' => $response));
});



$app->run();


