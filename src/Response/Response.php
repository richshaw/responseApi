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
    protected $collection;

    public function __construct($expId) {
        $this->expId = $expId;
        $this->collection = $expId . '_response';
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
     * Params we're going to use to create the response
     * @param  array $params
     */
    public function validate($params)
    {
        $v = new \Valitron\Validator($params);

        $v->rule('required', ['expId','participantId','input','slide','time']);
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
        $doc = array(
            'expId' => new \MongoId($this->expId),
            'participantId' => $this->participantId,
            'input' => $this->input,
            'slide' => $this->slide,
            'time' => $this->time
        );

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
        $this->setInput($doc['input']);
        $this->setSlide($doc['slide']);
        $this->setTime($doc['time']);

        return true;
    }

}
