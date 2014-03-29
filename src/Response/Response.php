<?php
namespace Response;

/**
* Timed response experiment response
*/
class Response
{

    /**
     * @var string
     */
    protected $id;

     /**
     * @var string
     */
    protected $expId;

    /**
     * @var string
     */
    protected $input;

    /**
     * @var int
     */
    protected $slide;

    /**
     * @var string
     */
    protected $participantId;

    /**
     * @var string
     */
    protected $sessionId;

     /**
     * @var timestamp
     */
    protected $created;

    /**
    * @var float
    */
    protected $time;

    /**
     * @var string
     */
    protected $collection;

    /**
     * @var mixed
     */
    protected $meta = array();


    public function __construct($expId) {
        //Cast to string incase id gets passed as MongoId
        $this->id = (string) new \MongoId();
        $this->expId = (string) $expId;
        $this->collection = (string) $expId . '_response';
        $this->created = strtotime('now');
    }

    /**
     * Get response id
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set response id
     * @param  string $title
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get experiment id
     * @return string
     */
    public function getExpId()
    {
        return $this->expId;
    }

    /**
     * Set experiment id
     * @param  string $expId
     */
    public function setExpId($expId)
    {
        $this->expId = $expId;
    }

    /**
     * Get partcipant id
     * @return string
     */
    public function getParticipantId()
    {
        return $this->participantId;
    }

    /**
     * Set participant id
     * @param  string $ParticipantId
     */
    public function setParticipantId($participantId)
    {
        $this->participantId = $participantId;
    }

    /**
     * Get session id
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * Set session id
     * @param  string $sessionId
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * Get partcipant response
     * @return string
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Set participant response
     * @param  string $input
     */
    public function setInput($input)
    {
        $this->input = $input;
    }

    /**
     * Get experiment slide
     * @return int
     */
    public function getSlide()
    {
        return $this->slide;
    }

    /**
     * Set experiment slide
     * @param  string $input
     */
    public function setSlide($slide)
    {
        $this->slide = $slide;
    }

    /**
     * Get response time
     * @return float
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set response time
     * @param  string $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * Get created date as UNIX time
     * @return timetsamp
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set created date as UNIX time
     * @param  mixed $random
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

     /**
     * Get meta data
     * @return meta
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Set response meta data
     * @param  mixed meta
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;
    }

    /**
     * Params we're going to use to create the response
     * @param  array $params
     */
    public function validate($params)
    {
        $v = new \Valitron\Validator($params);
        //expId already guaranteed by routing and _construct
        $v->rule('required', ['participantId','sessionId','input','slide','time']);
        $v->rule('integer', ['slide']);

        if($v->validate()) {
            return true;
        } else {
            return $v;
        }

    }

    /**
    * Save response to DB
    * @param  Database $db
    */
    public function save(Database $db) {
        //Doc to save to DB
        $doc = $this->toMongo();

        //Save new experiment
        $write = $db->insert($this->collection,$doc);
        $this->id = (string) $write['_id'];

        return true;
    }

    /**
    * Load response from DB
    * @param  string $id
    * @param  Database $db
    */
    public function load($id, Database $db) {
        $doc = $db->findOne($this->collection,$id);

        $this->setId((string) $doc['_id']);
        $this->setExpId((string) $doc['expId']);
        $this->setParticipantId($doc['participantId']);
        $this->setSessionId($doc['sessionId']);
        $this->setInput($doc['input']);
        $this->setSlide((int) $doc['slide']);
        $this->setTime((float) $doc['time']);
        $this->setCreated($doc['created']->sec);
        $this->setMeta($doc['meta']);

        return true;
    }

    /**
    * Return response as array
    */
    public function toArray() {
        return array(
            'id' => $this->getId(),
            'expId' => $this->getExpId(),
            'participantId' => $this->getParticipantId(),
            'sessionId' => $this->getSessionId(),
            'input' => $this->getInput(),
            'slide' => (int) $this->getSlide(),
            'time' => (float) $this->getTime(),
            'created' => $this->getCreated(),
            'meta' => $this->getMeta(),
        );
    }

    /**
    * Return as Mongo Doc ready for insert or update
    */
    public function toMongo() {
        //Get object as array
        $mongoDoc = $this->toArray();
        //Replace primitive types with MongoObjects where appropiate
        $mongoDoc['_id'] = new \MongoId($this->id);
        unset($mongoDoc['id']);
        $mongoDoc['expId'] = new \MongoId($this->getExpId());
        $mongoDoc['created'] = new \MongoDate($this->getCreated());

        return $mongoDoc;
    }
}
