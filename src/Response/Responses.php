<?php
namespace Response;

/**
* Loads multiple timed response experiments
*/
class Responses
{

    /**
     * @var string
     */
    protected $expId;

    /**
     * @var string
     */
    protected $collection;


    public function __construct($expId) {
        $this->expId = $expId;
        $this->collection = $expId . '_response';
    }

    /**
    * Load multiple responses from DB
    * @param  Database $db
    */
    public function load(Database $db) {
        $cursor = $db->find($this->collection);

        $responses = array();
        foreach ($cursor as $doc) {
            $response = new \Response\Response($doc['expId']);

            $response->setId((string) $doc['_id']);
            //$response->setExpId((string) $doc['expId']);
            //ExpId is set on construct
            $response->setParticipantId($doc['participantId']);
            $response->setSessionId($doc['sessionId']);
            $response->setInput($doc['input']);
            $response->setSlide($doc['slide']);
            $response->setParticipantSlide($doc['participantSlide']);
            $response->setTime($doc['time']);
            $response->setCreated($doc['created']->sec);
            $response->setError($doc['error']);
            $response->setMeta($doc['meta']);

            $responses[] = $response;
        }

        return $responses;
    }


    /**
    * Save multiple responses to DB
    * @param  Database $db
    */
    public function save(array $docs,Database $db) {
        //Save new experiment
        $db->batchInsert($this->collection,$docs);
        return true;
    }

    /**
    * Delete all responses from DB
    * @param  Database $db
    */
    public function delete(Database $db) {
        //Save new experiment
        $db->removeAll($this->collection);
        return true;
    }

    /**
    * Create responses collection
    * @param  Database $db
    */
    public function createCollection(Database $db) {
        $db->createCollection($this->collection);
        return true;
    }

    /**
    * Drop responses collection
    * @param  Database $db
    */
    public function dropCollection(Database $db) {
        $collection = $db->dropCollection($this->collection);
        return true;
    }

}
