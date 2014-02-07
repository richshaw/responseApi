<?php
require '../bootstrap.php';

$config = require '../config/global.php';
$db = new \Response\Database($config['Mongo']['uri']);
$app = new \Slim\Slim($config['Slim']);
$app->add(new \Middleware\Auth());
$app->view(new \View\Json());
$app->add(new \JsonApiMiddleware());
$app->add(new \Slim\Middleware\ContentTypes());

$app->get('/', function () use ($app) {
    $app->render(200,array('message' => "Timed response experiment **ONLINE**"));
});


/****
* Experiment routes
*/


$app->options('/experiment', function () use ($app) {
    $app->render(200);
});

$app->post('/experiment', function () use ($app,$db) {

    $request = $app->request();
    $params = $request->getBody();

    $experiment = new \Response\Experiment();

    $valid = $experiment->validate($params);

    if ($valid === true) {
        $experiment->setTitle($params['title']);
        $experiment->setBody($params['body']);
        $experiment->setInput($params['input']);
        $experiment->setError($params['error']);
        $experiment->setRandom($params['random']);

        $rules = array();
        foreach ($params['rules'] as $rule) {
            $ruleClass = '\Rule\\' . $rule['type'] . 'Rule';
            $rules[] = new $ruleClass($rule);
        }

        $experiment->setRules($rules);

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
        $data[] = $experiment->toArray();
    }

    $app->render(200,array('data' => $data));
});



$app->options('/experiment/:expId', function () use ($app) {
    $app->render(200);
});


$app->get('/experiment/:expId',  function ($expId) use ($app,$db) {

    $experiment = new \Response\Experiment();

    $experiment->load($expId,$db);

    $data = $experiment->toArray();

    $app->render(200,array('data' => $data));
});


$app->put('/experiment/:expId',  function ($expId) use ($app,$db) {

    $request = $app->request();
    $params = $request->getBody();

    $experiment = new \Response\Experiment();

    $valid = $experiment->validate($params);

    if ($valid === true) {
        $experiment->setId($expId);

        $experiment->setTitle($params['title']);
        $experiment->setBody($params['body']);
        $experiment->setInput($params['input']);
        $experiment->setError($params['error']);
        $experiment->setRandom($params['random']);
        $experiment->setMeta($params['meta']);

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
$app->options('/experiment/:expId/response', function () use ($app) {
    $app->render(200);
});

$app->options('/experiment/:expId/response/batch', function () use ($app) {
    $app->render(200);
});



$app->post('/experiment/:expId/response', function ($expId) use ($app,$db) {
    $request = $app->request();
    $params = $request->getBody();

    $response = new \Response\Response($expId);

    $valid = $response->validate($params);

    if ($valid === true) {
        $response->setExpId($expId);
        $response->setParticipantId($params['participantId']);
        $response->setSessionId($params['sessionId']);
        $response->setInput($params['input']);
        $response->setSlide($params['slide']);
        $response->setTime($params['time']);
        $response->setError($params['error']);
        $response->setParticipantSlide($params['participantSlide']);

        if(isset($params['meta'])) {
            $response->setMeta($params['meta']);
        }

        $response->save($db);

        $data = $response->getId();
        $app->render(200,array('data' => $data));
    }
    else {
        $errors = $valid->errors();
        $app->render(400,array('error' => true,'errors' => $errors));
    }
});

$app->post('/experiment/:expId/response/batch', function ($expId) use ($app,$db) {
    $request = $app->request();
    $params = $request->getBody();

    $responsesData = array();
    foreach ($params as $key => $value) {
        $response = new \Response\Response($expId);
        $valid = $response->validate($value);
        if ($valid === true) {
            $response->setExpId($expId);
            $response->setParticipantId($value['participantId']);
            $response->setSessionId($value['sessionId']);
            $response->setInput($value['input']);
            $response->setSlide($value['slide']);
            $response->setParticipantSlide($value['participantSlide']);
            $response->setTime($value['time']);
            $response->setCreated(strtotime('now'));
            $response->setError($value['error']);

            if(isset($value['meta'])) {
                $response->setMeta($value['meta']);
            }

            //Save new response object to array, format as Mongo Object ready for insert
            $responsesData[] = $response->toMongo();
        }
        else {
            $errors = $valid->errors();
            $app->render(400,array('error' => true,'errors' => $errors));
        }
    }

    $responses = new \Response\Responses($expId);
    $responses->save($responsesData,$db);

    $app->render(200);

});


$app->get('/experiment/:expId/response',  function ($expId) use ($app,$db) {

    $responses = new \Response\Responses($expId);

    $r = $responses->load($db);

    $data = array();
    foreach ($r as $response) {
        $data[] = $response->toArray();
    }

    $app->render(200,array('data' => $data));
});


$app->delete('/experiment/:expId/response',  function ($expId) use ($app,$db) {

    $responses = new \Response\Responses($expId);

    $responses->delete($db);

    $app->render(200);
});



$app->run();


