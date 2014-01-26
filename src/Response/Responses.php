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
            $response = new \Response\Response();

            $response->setId((string) $doc['_id']);
            $response->setExpId((string) $doc['expId']);
            $response->setParticipantId($doc['participantId']);
            $response->setInput($doc['input']);
            $response->setSlide($doc['slide']);
            $response->setTime($doc['time']);

            $responses[] = $response;
        }

        return $responses;
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
